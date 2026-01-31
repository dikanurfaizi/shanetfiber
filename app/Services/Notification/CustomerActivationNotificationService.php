<?php

namespace App\Services\Notification;

use App\DataTransferObjects\TelegramJobDto;
use App\Enums\TelegramAction;
use App\Enums\WhatsAppAction;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\User;
use App\Models\Receipt;
use App\Models\Bank;
use App\Models\Customers\CustomerSubscription;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Whatsapp\GowaService;
use App\Services\Telegrams\TelegramMessageService;
use App\Services\Whatsapp\CloudAPI\Messages\TemplateMessageService;

class CustomerActivationNotificationService
{
    public function __construct(
        private TemplateMessageService $templateMessageService,
        private GowaService $gowaService,
        private TelegramMessageService $telegramMessageService,
        private WhatsappGatewayResolver $gatewayResolver,
    ) {}

    public function send(
        ?int $customerMembershipId,
        CustomerSubscription $customerSubscription,
        Billing $billing,
        Receipt $receipt,
        string $traceId
    ): void {
        try {
            // Resolve WA account & gateway
            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, 'aktivasi')
                ?? [null, null];

            if (!$gateway || ! $account || !$number) {
                return; // resolver sudah log
            }

            if (empty($customerSubscription->customer->contact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customerSubscription->customer_id,
                ], 400, 'Customer has no contact for activation notification.', $traceId);
                return;
            }

            // Format nomor customer + admin
            $formatter = new FormatReceiver;

            $recipient = $formatter->formatReceiver($customerSubscription->customer->contact, null, false);

            $user = User::find($customerSubscription->customer->user_id);

            $adminNumber = $formatter->formatReceiver(
                $user->contact,
                'display',
                false
            );

            $dueDate = $billing->status === 'PL'
                ? $billing->paylater_until
                : $billing->due_date;

            // === Ambil semua bank user ===
            $bankAccounts = Bank::where('user_id', $user->id)
                ->where('disabled', 'false')->get();

            if ($bankAccounts->isEmpty()) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customerSubscription->customer_id,
                    'user_id' => $user->id,
                ], 200, 'User has no bank accounts, activation WA will show blank bank list.', $traceId);
            }

            /** Get Bank */
            $formattedBanks = $bankAccounts->map(
                fn($bank) =>
                "{$bank->bank} - {$bank->account_number} a.n {$bank->name}"
            )->implode(' | ');

            $customer = $billing->customer;

            $data = [
                'recipient' => $recipient,
                'customer_name' => $customer->name,
                'customer_id' => $customer->customer_id,
                'package_name' => $customer->active_package->name,
                'package_price' => $customer->active_package->price,
                'member_name' => $customer?->activeMembership ? $customer?->activeMembership?->membership?->name : 'Non Member',
                'admin_name' => $user->name,
                'admin_contact' => $adminNumber,
                'bank' => $formattedBanks,
                'account' => $account,
            ];

            if ($gateway->type === 'cloud') {
                $this->templateMessageService->sendCustomerActivation($data);
            } else {
                $this->gowaService->activation(
                    $customerMembershipId,
                    $customerSubscription,
                    WhatsAppAction::ACTIVATION->value,
                    $traceId
                );
            }

            $telegramJobData = new TelegramJobDto(
                customerId: $customerSubscription->customer_id,
                action: TelegramAction::ACTIVATION->value,
                traceId: $traceId,
                billingId: $billing->id,
                receiptId: $billing->billingPayment?->receipt?->id,
                chatId: null
            );

            $this->telegramMessageService->process($telegramJobData, $traceId);

            LogHelper::logInfo(null, [
                'customer_id' => $customerSubscription->customer_id,
                'billing_id' => $billing->id,
                'receipt_id' => $receipt->id,
                'gateway_type' => $gateway->type,
            ], 200, 'Customer activation notification dispatched', $traceId);
        } catch (\Throwable $e) {

            LogHelper::logError($e, [
                'customer_id' => $customerSubscription->customer_id,
                'billing_id' => $billing->id,
                'receipt_id' => $receipt->id,
                'error_message' => $e->getMessage(),
            ], 500, 'Failed to send activation notification', $traceId);
        }
    }
}
