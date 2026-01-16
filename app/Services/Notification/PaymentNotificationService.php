<?php

namespace App\Services\Notification;

use App\Enums\WhatsAppAction;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Receipt;
use App\Services\Whatsapp\CloudAPI\Messages\TemplateMessageService;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Whatsapp\GowaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;

class PaymentNotificationService
{
    public function __construct(
        private TemplateMessageService $templateMessageService,
        private GowaService $gowaService,
        private WhatsappGatewayResolver $gatewayResolver,
    ) {}

    /**
     * Dipanggil dari Job: load receipt, lalu kirim WA.
     */
    public function sendWhatsAppPaymentNotificationByReceiptId(int $receiptId, string $traceId): void
    {
        $receipt = Receipt::with([
            'billingPayment.billing.customer',
            'billingPayment.billing.billingSubscription',
        ])
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
        $type = 'cloud';
        try {
            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, $type)
                ?? [null, null, null];

            if (!$gateway || !$account || !$number) {
                return; // sudah dilog di resolver
            }

            $formatter = new FormatReceiver;

            $recipient = $formatter->formatReceiver(
                $receipt->billingPayment->billing->customer->contact,
                null,
                false
            );

            $paymentTime = Carbon::parse($receipt->payment_time)
                ->setTimezone('Asia/Jakarta')
                ->locale('id')
                ->translatedFormat('d F Y, H:i') . ' WIB';

            $token = Crypt::encryptString($receipt->slug);
            $token = str_replace(['+', '/', '='], ['-', '_', ''], $token);

            $data = [
                'recipient' => $recipient,
                'customer_id' => $receipt->customer_id,
                'customer_name' => $receipt->customer_name,
                'period' => $receipt->periode,
                'amount' => 'Rp ' . number_format($receipt->price_after_discount, 0, ',', '.'),
                'payment_time' => $paymentTime,
                'teller_name' => $receipt->teller_name,
                'receipt_code' => $token,
                'account' => $account,
            ];

            LogHelper::logInfo(null, ['data' => $data], 200, "Detail data", $traceId);

            if ($gateway->type === 'cloud') {
                $this->templateMessageService->sendInfoPembayaran($data);
            } else {
                $this->gowaService->notifyPaymentCompleted(
                    $receipt->billingPayment->billing,
                    $receipt,          // ⬅️ WAJIB
                    $receipt->payment_methode,            // cash | transfer | payment_gateway
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
