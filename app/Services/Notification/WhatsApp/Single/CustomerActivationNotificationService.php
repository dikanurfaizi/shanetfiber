<?php

namespace App\Services\Notification\WhatsApp\Single;

use App\Enums\WhatsAppAction;
use App\Helpers\DummyReceiptHelper;
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Customers\CustomerSubscription;
use App\Models\Membership\CustomerMembership;
use App\Models\Receipt;
use App\Models\User;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Handlers\ActivationHandler;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use App\Services\WhatsApp\WhatsAppGatewayResolver;
use Illuminate\Support\Carbon;
use Throwable;

class CustomerActivationNotificationService extends AbstractSingleNotificationService
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
        ?int $customerMembershipId,
        CustomerSubscription $customerSubscription,
        Billing $billing,
        Receipt $receipt,
        ?int $eventId,
        string $traceId
    ): void {
        $timer = new ExecutionTimer();

        LogHelper::logInfo(null, [], 100, "[CustomerActivationNotificationService] Start Service Running", $traceId, $timer->lap());

        try {
            $context = $this->resolveGatewayContext($action->value, $traceId, $timer);
            if (!$context) return;

            $gateway = $context['gateway'];
            $account = $context['account'];

            if (empty($customerSubscription->customer->contact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customerSubscription->customer_id,
                ], 400, 'Customer has no contact for activation notification.', $traceId, $timer->lap());
                return;
            }

            // Centralized Data Preparation
            $activationData = $this->prepareActivationData($customerSubscription, $billing, $customerMembershipId, $traceId);

            if ($gateway->type === 'cloud') {
                $data = [
                    'recipient'          => $activationData['recipient'],
                    'customer_name'      => $activationData['customer_name'],
                    'customer_id'        => $activationData['customer_code'],
                    'package_name'       => $activationData['package_name'],
                    'package_price'      => $activationData['package_price'],
                    'calculation_method' => $activationData['calculation_method'],
                    'prorate_note'       => $activationData['prorate_note'],
                    'grand_total'        => $activationData['grand_total_formatted'],
                    'member_type'        => $activationData['member_type'],
                    'admin_name'         => $activationData['admin_name'],
                    'admin_contact'      => $activationData['admin_contact'],
                    'bank'               => $activationData['admin_bank_list'],
                    'event_id'           => $eventId,
                    'account'            => $account,
                ];

                $this->templateMessageService->sendCustomerActivation($data);
            } else {
                $this->dispatcher->dispatch(
                    action: $action->value,
                    handlerFactory: fn(string $category) => new ActivationHandler(
                        action: $action,
                        customerSubscription: $customerSubscription,
                        builder: $this->builder,
                        activationData: $activationData,
                        category: $category
                    ),
                    traceId: $traceId,
                    eventId: $eventId,
                    deviceId: $context['deviceId']
                );
            }

            LogHelper::logInfo(null, [
                'customer_id'  => $customerSubscription->customer_id,
                'billing_id'   => $billing->id,
                'receipt_id'   => $receipt->id,
                'gateway_type' => $gateway->type,
            ], 200, 'Customer activation notification dispatched successfully.', $traceId, $timer->lap());
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'customer_id'     => $customerSubscription->customer_id,
                'billing_id'      => $billing->id,
                'receipt_id'      => $receipt->id,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, 'Failed to send activation notification', $traceId, $timer->lap());
        }
    }

    /**
     * Menyiapkan dan mengkalkulasi seluruh data kebutuhan notifikasi aktivasi.
     */
    private function prepareActivationData(
        CustomerSubscription $customerSubscription,
        Billing $billing,
        ?int $customerMembershipId,
        string $traceId
    ): array {
        $customer = $customerSubscription->customer;
        $user = User::find($customer->user_id);

        // Receiver & Admin formatting
        $recipient = $this->formatNumber->formatReceiver($customer->contact, null, false);
        $adminContact = $this->formatNumber->formatReceiver($user->contact ?? '', 'display', false);

        // Bank Accounts Collection
        $bankAccounts = $this->bankCollector->getBanksByUserId($user->id ?? 0);
        if ($bankAccounts->isEmpty()) {
            LogHelper::logWarning(null, [
                'customer_id' => $customerSubscription->customer_id,
                'user_id'     => $user->id ?? null,
            ], 200, 'User has no bank accounts, activation WA will show blank bank list.', $traceId);
        }

        $formattedBanksList = $bankAccounts->map(
            fn($bank) => "{$bank->bank} - {$bank->account_number} a.n {$bank->name}"
        )->implode(' | ');

        $adminBankInfo = $this->bankCollector->getFormattedBankInfo($user->id ?? 0, $traceId);

        // Membership Determination
        if ($customerMembershipId === null) {
            $memberType = $customer->activeMembership?->membership?->name ?? 'Non Member';
        } else {
            $customerMembership = CustomerMembership::with('membership')->find($customerMembershipId);
            $memberType = $customerMembership?->membership->name ?? 'Non Member';
        }

        // Prorate Calculation
        $createdAt = Carbon::parse($customer->created_at);
        $isProrate = $createdAt->day > 15 ? 1 : 0;
        $totalDaysInMonth = $createdAt->daysInMonth;
        $activeDays = ($totalDaysInMonth - $createdAt->day) + 1;

        $calculationMethod = ($isProrate === 1) ? 'Prorate' : 'Biaya Penuh';
        $prorateNote = ($isProrate === 1) ? "_(Dihitung proporsional {$activeDays} hari s.d akhir bulan)_" : '';

        // Fallback Price Calculation
        $fallbackPrice = $customerSubscription->package->price ?? 0;
        $firstPeriod = $customerSubscription->billingSubscriptionPeriodes->first();
        if ($firstPeriod && $firstPeriod->billings) {
            $actualBilling = $firstPeriod->billings instanceof \Illuminate\Support\Collection
                ? $firstPeriod->billings->first()
                : $firstPeriod->billings;

            if ($actualBilling) {
                $fallbackPrice = $actualBilling->total_amount ?? $fallbackPrice;
            }
        }

        $grandTotal = $billing->total_amount ?? 0;

        return [
            'recipient'             => $recipient,
            'customer_name'         => $customer->name,
            'customer_code'         => $customer->customer_id,
            'package_name'          => $customerSubscription->package->name ?? '-',
            'package_price'         => $customerSubscription->package->price ?? 0,
            'calculation_method'    => $calculationMethod,
            'prorate_note'          => $prorateNote,
            'grand_total_amount'    => $grandTotal,
            'grand_total_formatted' => 'Rp ' . number_format($grandTotal, 0, ',', '.'),
            'fallback_price'        => $fallbackPrice,
            'member_type'           => $memberType,
            'admin_name'            => $user->name ?? '-',
            'admin_contact'         => $adminContact,
            'admin_bank_list'       => $formattedBanksList,
            'admin_bank_info'       => $adminBankInfo,
        ];
    }
}
