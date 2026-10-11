<?php

namespace App\Services\Notification\WhatsApp\Single;

use App\Enums\WhatsAppAction;
use App\Helpers\DummyReceiptHelper;
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Customers\CustomerAddon;
use App\Models\Customers\CustomerSubscription;
use App\Models\User;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Handlers\BillingStatusHandler;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Notification\WhatsApp\Single\AbstractSingleNotificationService;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use App\Services\WhatsApp\WhatsAppGatewayResolver;
use Illuminate\Support\Carbon;
use Throwable;

class PaymentCancellationNotificationService extends AbstractSingleNotificationService
{
    public function __construct(
        WhatsAppGatewayResolver $gatewayResolver,
        TemplateMessageService $templateMessageService,
        MessageDispatcher $dispatcher,
        AdminBankCollector $bankCollector,
        FormatReceiver $formatNumber,
        MessageBuilder $builder,
        DummyReceiptHelper $dummyReceipt
    ) {
        parent::__construct($gatewayResolver, $templateMessageService, $dispatcher, $bankCollector, $formatNumber, $builder, $dummyReceipt);
    }

    public function send(
        WhatsAppAction $action,
        ?int $receiptId,
        int $billingId,
        ?array $receiptData,
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

            $billing = Billing::with(['customer.user'])->find($billingId);

            if (!$billing || !$billing->customer) {
                LogHelper::logError(null, [
                    'billing_id'  => $billingId,
                    'customer_id' => $billing?->customer_id,
                ], 404, 'Billing or Customer not found for payment cancellation notification', $traceId, $timer->lap());
                return;
            }

            if (empty($billing->customer->contact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $billing->customer_id,
                ], 400, 'Customer has no contact for payment cancellation notification.', $traceId, $timer->lap());
                return;
            }

            $to = $this->formatNumber->formatReceiver($billing->customer->contact, null, false);

            if ($gateway->type === 'cloud') {
                $data = [
                    'recipient'        => $to,
                    'customer_name'    => $billing->customer->name,
                    'customer_id'      => $billing->customer->customer_id,
                    'billing_number'   => $billing->billing_number,
                    'account'          => $account,
                    'event_id'         => $eventId,
                ];

                // $this->templateMessageService->sendPaymentCancellation($data);
            } else {
                $receipt = $this->dummyReceipt->createUnpayment(
                    $billing->id,
                    $action->value,
                    $billing->receipt_id,
                    $receiptData,
                    $traceId
                );

                if (!$receipt) {
                    LogHelper::logWarning(null, [
                        'billing_id' => $billing->id,
                    ], 404, 'No receipt could be resolved for cancel payment', $traceId, $timer->lap());
                    return;
                }

                $customerSubscription = $billing->billingSubscription?->customerSubscription;

                if (!$customerSubscription) {
                    LogHelper::logWarning(null, [
                        'billing_id'  => $billing->id,
                        'customer_id' => $billing->customer_id,
                    ], 404, 'Customer subscription not found for payment cancellation notification.', $traceId, $timer->lap());
                    return;
                }

                // 2. Prepare Data Secara Terpusat Sesuai Ekspektasi BillingStatusHandler
                $billingData = $this->prepareBillingData($billing, $customerSubscription, $traceId);

                // Direct Dispatch Handler ke GOWA (Local Engine)
                $this->dispatcher->dispatch(
                    action: $action->value,
                    handlerFactory: fn(string $category) => new BillingStatusHandler(
                        action: $action,
                        builder: $this->builder,
                        billingData: $billingData,
                        category: $category
                    ),
                    traceId: $traceId,
                    eventId: $eventId,
                    deviceId: $context['deviceId']
                );
            }

            LogHelper::logInfo(null, [
                'billing_id'   => $billingId,
                'customer_id'  => $billing->customer_id,
                'receipt_id'   => $receiptId,
                'gateway_type' => $gateway->type,
            ], 200, 'Payment cancellation notification dispatched successfully.', $traceId, $timer->lap());
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'billing_id'      => $billingId,
                'customer_id'     => $billing->customer_id ?? null,
                'receipt_id'      => $receiptId,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, 'Failed to send cancellation notification', $traceId, $timer->lap());
        }
    }

    /**
     * Menyiapkan array data terstruktur sesuai ekspektasi BillingStatusHandler.
     */
    private function prepareBillingData(
        Billing $billing,
        CustomerSubscription $customerSubscription,
        string $traceId
    ): array {
        $customer = $billing->customer;

        // Load relasi pendukung secara efisien
        $customerSubscription->loadMissing([
            'customer.activeMembership.membership',
            'package',
        ]);
        $billing->loadMissing(['customer.user', 'billingSubscription.billingPeriode']);

        $user = $customer->user;
        $userUpdate = User::find($billing->updated_by);

        // Fetch Addons Aktif
        $addons = CustomerAddon::where('customer_id', $billing->customer_id)
            ->where('is_active', true)
            ->get();

        // Bank Info
        $adminUserId = (int) ($customer->user_id ?? 1);
        $adminBankInfo = $this->bankCollector->getFormattedBankInfo($adminUserId, $traceId);

        // Format Nama Paket & Periode
        $packageName = $customerSubscription->package?->name ?? $billing->package_name ?? '-';
        $periode = $billing->billingSubscription?->billingPeriode?->name
            ?? $billing->periode?->display_name
            ?? '-';

        $paylaterFormatted = $billing->paylater_until
            ? Carbon::parse($billing->paylater_until)->locale('id')->translatedFormat('d F Y')
            : '-';

        return [
            'billing_id'         => $billing->id,
            'customer_id'        => $customer->id,
            'customer_code'      => $customer->customer_id ?? $customer->id,
            'customer_name'      => $customer->name ?? '-',
            'target_contact'     => $customer->contact ?? '',
            'billing_number'     => $billing->billing_number ?? 'Nomor tidak tersedia',
            'package_name'       => $packageName,
            'periode'            => $periode,
            'base_price'         => $billing->base_price ?? 0,
            'total_amount'       => $billing->total_amount ?? 0,
            'discount_amount'    => $billing->discount_amount ?? 0,
            'addons'             => $addons,
            'membership_name'    => optional($customer->activeMembership?->membership)->name,
            'admin_name'         => $user?->name ?? 'Admin',
            'admin_contact'      => $user?->contact ?? '-',
            'teller_name'        => $userUpdate?->name ?? 'System',
            'admin_bank_info'    => $adminBankInfo,
            'paylater_formatted' => $paylaterFormatted,
        ];
    }
}
