<?php

namespace App\Services\Notification;

use App\DataTransferObjects\TelegramJobDto;
use App\Enums\TelegramAction;
use App\Enums\WhatsAppAction;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Billing\Billing;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\Bank;
use App\Services\Whatsapp\CloudAPIService;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Whatsapp\GowaService;
use App\Services\Telegrams\TelegramMessageService;

class CustomerActivationNotificationService
{
    public function __construct(
        private CloudAPIService $cloudAPIService,
        private GowaService $gowaService,
        private TelegramMessageService $telegramMessageService,
        private WhatsappGatewayResolver $gatewayResolver,
    ) {}

    public function send(
        Customer $customer,
        Billing $billing,
        Receipt $receipt,
        string $traceId
    ): void {
        try {
            // Resolve WA account & gateway
            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId)
                ?? [null, null];

            if (!$gateway || ! $account || !$number) {
                return; // resolver sudah log
            }

            if (empty($customer->contact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customer->id,
                ], 400, 'Customer has no contact for activation notification.', $traceId);
                return;
            }

            // Format nomor customer + admin
            $formatter = new FormatReceiver;

            $to = $formatter->formatReceiver($customer->contact, null, false);

            $adminNumber = $formatter->formatReceiver(
                $customer->user?->contact,
                'display',
                false
            );

            // === Ambil semua bank user ===
            $bankAccounts = Bank::where('user_id', $customer->user->id)
                ->where('disabled', 'false')->get();

            if ($bankAccounts->isEmpty()) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customer->id,
                    'user_id' => $customer->user->id,
                ], 200, 'User has no bank accounts, activation WA will show blank bank list.', $traceId);
            }

            /** Get Bank */
            $formattedBanks = $bankAccounts->map(
                fn($bank) =>
                "{$bank->bank} - {$bank->account_number} a.n {$bank->name}"
            )->implode(' | ');

            /** Template Param */
            $templateParams = [
                $customer->name, // {{1}}
                $customer->customer_id ?? $customer->id, // {{2}}
                $customer->package->name, // {{3}}
                'Rp ' . number_format($customer->package->price, 0, ',', '.'), // {{4}}
                optional($customer->member)->type ?? 'Non Member', // {{5}}
                $customer->user->name, // {{6}} admin name
                $adminNumber, // {{7}} admin phone
                $formattedBanks, // {{8}} daftar bank
            ];

            if ($gateway->type === 'cloud') {
                $this->cloudAPIService->sendTemplateMessage(
                    account: $account,
                    templateName: 'aktivasi_layanan_pelanggan',
                    phoneNumberId: $number->meta_phone_number_id,
                    to: $to,
                    bodyParams: $templateParams,
                    buttonParams: [],
                    traceId: $traceId,
                );
            } else {
                $this->gowaService->activation(
                    $customer->id,
                    WhatsAppAction::ACTIVATION->value,
                    $traceId
                );
            }

            $telegramJobData = new TelegramJobDto(
                customerId: $customer->id,
                action: TelegramAction::ACTIVATION->value,
                traceId: $traceId,
                billingId: null,
                receiptId: null,
                chatId: null
            );

            $this->telegramMessageService->process($telegramJobData, $traceId);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'billing_id' => $billing->id,
                'receipt_id' => $receipt->id,
                'gateway_type' => $gateway->type,
            ], 200, 'Customer activation notification dispatched', $traceId);
        } catch (\Throwable $e) {

            LogHelper::logError($e, [
                'customer_id' => $customer->id,
                'billing_id' => $billing->id,
                'receipt_id' => $receipt->id,
                'error_message' => $e->getMessage(),
            ], 500, 'Failed to send activation notification', $traceId);
        }
    }
}
