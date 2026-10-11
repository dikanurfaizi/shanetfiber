<?php

namespace App\Services\Notification\WhatsApp\Single;

use App\Enums\WhatsAppAction;
use App\Exceptions\PackageNotFoundException;
use App\Helpers\DelayMessage;
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
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use App\Services\WhatsApp\WhatsAppGatewayResolver;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class PaymentPayLaterNotificationService extends AbstractSingleNotificationService
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
        int $billingId,
        ?int $eventId,
        string $traceId,
        ?string $overrideContact = null
    ): void {
        $timer = new ExecutionTimer();

        try {
            // 1. RESOLVE GATEWAY CONTEXT
            $context = $this->resolveGatewayContext($action->value, $traceId, $timer);
            if (!$context) return;

            $gateway = $context['gateway'];
            $account = $context['account'];

            // 2. LOAD BILLING & VALIDASI CUSTOMER
            $billing = Billing::with(['customer', 'billingSubscription', 'updatedByUser'])
                ->find($billingId);

            if (!$billing || !$billing->customer) {
                LogHelper::logError(null, [
                    'billing_id'  => $billingId,
                    'customer_id' => $billing?->customer_id,
                ], 404, '[PaymentPayLaterNotificationService] Billing or Customer not found', $traceId, $timer->lap());

                return;
            }

            if (empty($billing->customer->contact) && empty($overrideContact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $billing->customer->id,
                ], 400, '[PaymentPayLaterNotificationService] Customer has no contact number', $traceId, $timer->lap());

                return;
            }

            // 3. PREPARE DATA UNTUK HANDLER & CLOUD API
            $billingData = $this->prepareBillingData($billing, $overrideContact, $traceId);

            // 4. BRANCHING GATEWAY ENGINE
            if ($gateway->type === 'cloud') {
                $recipient = $this->formatNumber->formatReceiver($billingData['target_contact'], null, false);

                $token = Crypt::encryptString($billing->slug);
                $token = str_replace(['+', '/', '='], ['-', '_', ''], $token);

                $data = [
                    'recipient'     => $recipient,
                    'customer_id'   => $billing->customer_id,
                    'customer_name' => $billing->customer->name,
                    'period'        => $billingData['periode'],
                    'teller_name'   => $billingData['teller_name'],
                    'account'       => $account,
                    'paylater_code' => $token,
                    'event_id'      => $eventId,
                ];

                $this->templateMessageService->sendInfoPayLater($data);
            } else {
                // LOCAL ENGINE (MESSAGE DISPATCHER)
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
                'billing_id'  => $billingId,
                'customer_id' => $billing->customer->id,
                'gateway'     => $gateway->type,
            ], 200, '[PaymentPayLaterNotificationService] Notification dispatched successfully', $traceId, $timer->lap());
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'billing_id'      => $billingId,
                'customer_id'     => $billing->customer->id ?? null,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, '[PaymentPayLaterNotificationService] Failed sending PayLater notification', $traceId, $timer->lap());
        }
    }

    /**
     * Menyiapkan dan memuat seluruh data terkait billing secara terpusat.
     */
    private function prepareBillingData(
        Billing $billing,
        ?string $overrideContact,
        string $traceId
    ): array {
        $customer = $billing->customer;

        if (!$customer) {
            throw new PackageNotFoundException("Customer not found for Billing ID: {$billing->id}");
        }

        // Subscription & Relation Loading
        $subscription = CustomerSubscription::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->first();

        if ($subscription) {
            $subscription->loadMissing([
                'customer.activeMembership.membership',
                'package',
            ]);
        }

        $billing->loadMissing(['customer.user', 'billingSubscription.billingPeriode']);

        $user = $customer->user;
        $userUpdate = User::find($billing->updated_by);

        // Fetch Addons
        $addons = CustomerAddon::where('customer_id', $billing->customer_id)
            ->where('is_active', true)
            ->get();

        // Bank Info
        $adminUserId = (int) ($customer->user_id ?? 1);
        $adminBankInfo = $this->bankCollector->getFormattedBankInfo($adminUserId, $traceId);

        // Format Values
        $targetContact = $overrideContact ?: $customer->contact;
        $packageName = $subscription?->package?->name ?? $billing->package_name ?? '-';
        $periode = $billing->billingSubscription?->billingPeriode->name
            ?? $billing->periode->display_name
            ?? '-';

        $paylaterFormatted = $billing->paylater_until
            ? Carbon::parse($billing->paylater_until)->locale('id')->translatedFormat('d F Y')
            : '-';

        return [
            'billing_id'         => $billing->id,
            'customer_id'        => $customer->id,
            'customer_code'      => $customer->customer_id ?? $customer->id,
            'customer_name'      => $customer->name ?? '-',
            'target_contact'     => $targetContact,
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
