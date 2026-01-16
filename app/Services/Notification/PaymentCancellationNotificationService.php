<?php

namespace App\Services\Notification;

use App\DataTransferObjects\TelegramJobDto;
use App\Enums\TelegramAction;
use App\Enums\WhatsAppAction;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Helpers\DummyReceiptHelper;
use App\Models\Billings\Billing;
// use App\Services\Whatsapp\CloudAPIService;
use App\Services\Whatsapp\GowaService;
use App\Services\Telegrams\TelegramMessageService;
use App\Services\Whatsapp\WhatsappGatewayResolver;

class PaymentCancellationNotificationService
{
    public function __construct(
        // private CloudAPIService $cloudAPIService,
        private GowaService $gowaService,
        private TelegramMessageService $telegramMessageService,
        private WhatsappGatewayResolver $gatewayResolver,
        private DummyReceiptHelper $dummyReceipt
    ) {}

    public function send(
        ?int $receiptId,
        int $billingId,
        ?array $receiptData,
        string $traceId
    ): void {
        $type = 'onprem';
        try {
            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, $type)
                ?? [null, null, null];

            if (! $gateway || ! $account || ! $number) {
                return; // sudah dilog di resolver
            }

            $billing = Billing::with('customer')->find($billingId);

            if (! $billing || ! $billing->customer) {
                LogHelper::logError(null, [
                    'billing_id' => $billingId,
                    'customer_id' => $billing?->customer_id,
                ], 404, 'Billing or Customer not found for payment cancellation notification', $traceId);

                return;
            }

            if (empty($billing->customer->contact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $billing?->customer_id,
                ], 400, 'Customer has no contact for payment cancellation notification.', $traceId);

                return;
            }

            $formatter = new FormatReceiver;

            $to = $formatter->formatReceiver(
                $billing->customer->contact,
                null,
                false
            );

            if ($gateway->type === 'cloud') {
                // $this->cloudAPIService->sendTemplateMessage(
                //     account: $account,
                //     templateName: 'pembatalan_pembayaran',
                //     phoneNumberId: $number->meta_phone_number_id,
                //     to: $to, // 628xxx
                //     bodyParams: [
                //         $billing->customer->name,   // {{1}}
                //         $billing->customer->customer_id,   // {{2}} mis: SNF02-00220
                //         $billing->billing_number,  // {{3}} mis: INV-202511-418-401
                //     ],
                //     buttonParams: [],    // QUICK_REPLY tidak pakai parameter
                //     traceId: $traceId,
                // );
            } else {
                $receipt = $this->dummyReceipt->createUnpayment(
                    $billing->id,
                    WhatsAppAction::UNPAYMENT->value,
                    $billing->receipt_id,
                    $receiptData,
                    $traceId
                );

                if (! $receipt) {
                    LogHelper::logWarning(null, [
                        'billing_id' => $billing->id,
                    ], 404, 'No receipt could be resolved for cancel payment', $traceId);

                    return;
                }

                $this->gowaService->notifyUnpayment(
                    $billing->billingSubscription->customerSubscription,
                    $billing,
                    $traceId
                );
            }

            // === TELEGRAM ===
            $telegramDto = new TelegramJobDto(
                customerId: $billing->customer_id,
                action: TelegramAction::UNPAYMENT->value,
                traceId: $traceId,
                billingId: $billingId,
                receiptId: $receiptId,
                extra: [],
                chatId: null
            );

            $this->telegramMessageService->process($telegramDto, $traceId);

            LogHelper::logInfo(null, [
                'billing_id' => $billingId,
                'customer_id' => $billing->customer_id,
                'receipt_id' => $receiptId,
            ], 200, 'Payment cancellation notification dispatched', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'billing_id' => $billingId,
                'customer_id' => $billing->customer_id ?? null,
                'receipt_id' => $receiptId,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500, 'Failed to send cancellation notification', $traceId);
        }
    }
}
