<?php

namespace App\Services\Notification;

use App\DataTransferObjects\TelegramJobDto;
use App\Enums\TelegramAction;
use App\Enums\WhatsappAction;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Billing\Billing;
use App\Services\Whatsapp\CloudAPIService;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Whatsapp\GowaService;
use App\Services\Telegrams\TelegramMessageService;
use Carbon\Carbon;

class PaymentPayLaterNotificationService
{
    public function __construct(
        private CloudAPIService $cloudAPIService,
        private GowaService $gowaService,
        private TelegramMessageService $telegramMessageService,
        private WhatsappGatewayResolver $gatewayResolver,
    ) {}

    public function send(
        int $billingId,
        string $traceId
    ): void {
        try {
            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId)
                ?? [null, null];

            if (! $gateway || ! $account || !$number) {
                return;
            }

            $billing = Billing::with(['customer', 'receipt', 'billingSubscription'])
                ->findOrFail($billingId);

            if (! $billing || ! $billing->customer) {
                LogHelper::logError(null, [
                    'billing_id' => $billingId,
                    'customer_id' => $billing?->customer_id,
                ], 404, 'Billing or Customer not found for payment cancellation notification', $traceId);

                return;
            }

            if (empty($billing->customer->contact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $billing->customer->id,
                ], 404, 'Customer has no contact.', $traceId);

                return;
            }

            $formatter = new FormatReceiver;

            $to = $formatter->formatReceiver($billing->customer->contact, null, false);

            if ($gateway->type === 'cloud') {
                $this->cloudAPIService->sendTemplateMessage(
                    account: $account,
                    templateName: 'pembayaran_paylater',
                    phoneNumberId: $account->meta_phone_number_id,
                    to: $to,
                    bodyParams: [
                        $billing->customer->name,                                   // {{1}}
                        $billing->billingSubscription->billing_periode,    // {{2}}
                        'Rp ' . number_format($billing->amount, 0, ',', '.00'),   // {{3}}
                        Carbon::parse($billing->pay_later)->format('d F Y'),      // {{4}}
                        $billing->billing_number,                          // {{5}}
                        $billing->teller_name,                              // {{6}}
                    ],
                    buttonParams: [
                        0 => $billing->billing_number,   // untuk URL button index 0
                    ],
                    traceId: $traceId,
                );
            } else {
                $this->gowaService->payment_package(
                    $billing,
                    $billing->receipt,
                    WhatsAppAction::PAYLATER->value,
                    $traceId
                );
            }

            // TELEGRAM
            $telegramDto = new TelegramJobDto(
                customerId: $billing->customer->id,
                action: TelegramAction::PAYLATER_PAYMENT->value,
                traceId: $traceId,
                billingId: $billingId,
                receiptId: null,
                extra: [],
                chatId: null
            );

            $this->telegramMessageService->process($telegramDto, $traceId);

            LogHelper::logInfo(null, [
                'billing_id' => $billingId,
                'customer_id' => $billing->customer->id,
            ], 200, 'PayLater notification dispatched', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError($e, [
                'billing_id' => $billingId,
                'customer_id' => $billing->customer->id ?? null,
            ], 500, 'Failed sending PayLater notification', $traceId);
        }
    }
}
