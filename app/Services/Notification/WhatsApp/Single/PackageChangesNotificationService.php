<?php

namespace App\Services\Notification\WhatsApp\Single;

use App\Enums\WhatsAppAction;
use App\Helpers\DelayMessage;
use App\Helpers\DummyReceiptHelper;
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Handlers\ChangesPackageHandler;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use App\Services\WhatsApp\WhatsAppGatewayResolver;
use Throwable;

class PackageChangesNotificationService extends AbstractSingleNotificationService
{
    public function __construct(
        WhatsAppGatewayResolver $gatewayResolver,
        TemplateMessageService $templateMessageService,
        MessageDispatcher $dispatcher,
        AdminBankCollector $bankCollector,
        FormatReceiver $formatNumber,
        MessageBuilder $builder,
        DummyReceiptHelper $dummyReceipt,
        private DelayMessage $delayMessage
    ) {
        parent::__construct($gatewayResolver, $templateMessageService, $dispatcher, $bankCollector, $formatNumber, $builder, $dummyReceipt);
    }

    public function send(
        WhatsAppAction $action,
        Customer $customer,
        string $oldPackageName,
        int $oldPackagePrice,
        ?int $eventId,
        string $traceId
    ): void {
        $timer = new ExecutionTimer();

        try {
            // 1. Resolve Gateway Context
            $context = $this->resolveGatewayContext($action->value, $traceId, $timer);
            if (!$context) return;

            $gateway = $context['gateway'];
            $account = $context['account'];

            if (empty($customer->contact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customer->id,
                ], 400, 'Customer has no contact for package-change notification.', $traceId, $timer->lap());
                return;
            }

            // Eager load relasi yang dibutuhkan jika belum diload
            $customer->loadMissing(['user', 'active_package', 'member']);

            $newPackage = $customer->active_package;

            if (!$newPackage) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customer->id,
                ], 404, 'Customer has no new package relation for package-change notification.', $traceId, $timer->lap());
                return;
            }

            // 2. Prepare Data Secara Terpusat
            $packageData = $this->preparePackageData($customer, $oldPackageName, $oldPackagePrice, $traceId);

            if ($gateway->type === 'cloud') {
                $data = [
                    'recipient'         => $packageData['target_contact'],
                    'customer_name'     => $customer->name,
                    'customer_id'       => $packageData['customer_code'],
                    'old_package_name'  => $oldPackageName,
                    'old_package_price' => $oldPackagePrice,
                    'new_package_name'  => $newPackage->name,
                    'new_package_price' => $newPackage->price,
                    'admin_name'        => $packageData['admin_name'],
                    'admin_contact'     => $packageData['admin_contact'],
                    'account'           => $account,
                    'event_id'          => $eventId,
                ];

                // $this->templateMessageService->sendPackageChanges($data);
            } else {
                // Direct Dispatch Handler ke GOWA via MessageDispatcher
                $this->dispatcher->dispatch(
                    action: $action->value,
                    handlerFactory: fn(string $category) => new ChangesPackageHandler(
                        action: $action,
                        builder: $this->builder,
                        packageData: $packageData,
                        category: $category
                    ),
                    traceId: $traceId,
                    eventId: $eventId,
                    deviceId: $context['deviceId']
                );
            }

            LogHelper::logInfo(null, [
                'customer_id'  => $customer->id,
                'old_package'  => $oldPackageName,
                'old_price'    => $oldPackagePrice,
                'new_package'  => $newPackage->name,
                'new_price'    => $newPackage->price,
                'gateway_type' => $gateway->type,
            ], 200, 'Package change notification dispatched successfully.', $traceId, $timer->lap());
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'customer_id'     => $customer->id,
                'old_package'     => $oldPackageName,
                'old_price'       => $oldPackagePrice,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, 'Failed to send package-change notification', $traceId, $timer->lap());
        }
    }

    /**
     * Menyiapkan array data terstruktur untuk kebutuhan notifikasi perubahan paket.
     */
    private function preparePackageData(
        Customer $customer,
        string $oldPackageName,
        int $oldPackagePrice,
        string $traceId
    ): array {
        $to = $this->formatNumber->formatReceiver($customer->contact, null, false);
        $adminNumber = $this->formatNumber->formatReceiver($customer->user?->contact ?? '', 'display', false);

        $adminUserId = (int) ($customer->user_id ?? 1);
        $adminBankInfo = $this->bankCollector->getFormattedBankInfo($adminUserId, $traceId);

        $newPackage = $customer->active_package;

        return [
            'customer_id'       => $customer->id,
            'customer_code'     => $customer->customer_id ?? $customer->id,
            'customer_name'     => $customer->name ?? '-',
            'customer_address'  => $customer->address ?? '-',
            'customer_email'    => $customer->email ?? '-',
            'target_contact'    => $to,
            'old_package_name'  => $oldPackageName,
            'old_package_price' => $oldPackagePrice,
            'new_package_name'  => $newPackage?->name ?? '-',
            'new_package_price' => $newPackage?->price ?? 0,
            'admin_name'        => $customer->user?->name ?? '-',
            'admin_contact'     => !empty($adminNumber) ? $adminNumber : '-',
            'admin_bank_info'   => $adminBankInfo,
            'member_type'       => optional($customer->member)->type ?? 'Non Member',
        ];
    }
}
