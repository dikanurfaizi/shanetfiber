<?php

namespace App\Services\Notification;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Receipt;
use App\Services\Whatsapp\CloudAPIService;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Whatsapp\GowaService;
use Carbon\Carbon;

class PaymentNotificationService
{
    public function __construct(
        private CloudAPIService $cloudAPIService,
        private GowaService $gowaService,
        private WhatsappGatewayResolver $gatewayResolver,
    ) {}

    /**
     * Dipanggil dari Job: load receipt, lalu kirim WA.
     */
    public function sendWhatsAppPaymentNotificationByReceiptId(int $receiptId, string $traceId): void
    {
        $receipt = Receipt::with(['billing.customer', 'billing.billingSubscription'])
            ->find($receiptId);

        if (! $receipt) {
            LogHelper::logWarning(null, [
                'receipt_id' => $receiptId,
            ], 404, 'Receipt not found for WhatsApp notification.', $traceId);

            return;
        }

        $this->sendWhatsAppPaymentNotification($receipt, $traceId);
    }

    /**
     * Core WA notification logic (boleh dipakai juga sync kalau perlu).
     */
    public function sendWhatsAppPaymentNotification($receipt, string $traceId): void
    {
        try {
            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId)
                ?? [null, null, null];

            if (! $gateway || ! $account || ! $number) {
                return; // sudah dilog di resolver
            }

            $formatter = new FormatReceiver;

            $to = $formatter->formatReceiver(
                $receipt->billing->customer->contact,
                null,
                false
            );

            $bodyParams = [
                $receipt->customer_name,
                $receipt->billing->billingSubscription->billing_periode,
                'Rp ' . number_format($receipt->amount_paid, 0, ',', '.00'),
                Carbon::parse($receipt->payment_time)->format('d F Y'),
                $receipt->number,
                $receipt->teller_name,
            ];

            if ($gateway->type === 'cloud') {
                $this->cloudAPIService->sendTemplateMessage(
                    account: $account,
                    templateName: 'info_pembayaran',
                    phoneNumberId: $account->meta_phone_number_id,
                    to: $to,
                    bodyParams: $bodyParams,
                    buttonParams: [
                        0 => $receipt->number,
                    ],
                    traceId: $traceId,
                );
            } else {
                $this->gowaService->payment_package(
                    $receipt->billing->customer,
                    $receipt->billing,
                    $receipt,
                    $receipt->payment_methode,
                    $traceId
                );
            }
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                ['exception' => $e->getMessage()],
                500,
                'Failed sending WhatsApp payment notification.',
                $traceId
            );
        }
    }
}
