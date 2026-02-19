<?php

namespace App\Services\Notification;

use App\Enums\WhatsAppAction;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Billings\Billing;
use App\Models\Billings\Order;
// use App\Services\Whatsapp\CloudAPIService;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Whatsapp\GowaService;
use App\Services\Telegrams\TelegramMessageService;
use Carbon\Carbon;

class OrderCreatedNotificationService
{
    public function __construct(
        // private CloudAPIService $cloudAPIService,
        private GowaService $gowaService,
        private TelegramMessageService $telegramMessageService,
        private WhatsappGatewayResolver $gatewayResolver,
    ) {}

    public function send(
        Customer $customer,
        Billing $billing,
        Order $order,
        ?int $eventId = null,
        string $traceId
    ): void {
        try {
            // === Resolve WA gateway & account ===
            [$gateway, $account, $number] =
                $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, 'pembayaran_online')
                ?? [null, null, null];

            if (! $gateway || ! $account || ! $number) {
                return; // resolver already logged
            }

            if (empty($customer->contact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customer->id,
                ], 400, 'Customer has no contact for order-created notification.', $traceId);
                return;
            }

            $formatter = new FormatReceiver();
            $to = $formatter->formatReceiver($customer->contact, null, false);

            // Admin number
            $adminNumber = $formatter->formatReceiver(
                $customer->user?->contact,
                'display',
                false
            );

            // === Tentukan template berdasarkan payment_type ===
            $paymentType = $order->payment_type;

            $templateName = match ($paymentType) {
                'E-Wallet'          => 'pembayaran_dompet_digital',
                'Convenience Store' => 'pembayaran_offline',
                'Virtual Account'   => 'pembayaran_virtual_account',
                default => 'pembayaran_virtual_account', // fallback aman
            };

            // Format expired
            $expiredAt = Carbon::createFromTimestamp((int) $order->expired_time)
                ->timezone('Asia/Jakarta')
                ->format('d F Y, H:i');

            // Parameter template (POSISIONAL)
            $templateParams = [
                $customer->name,                                        // {{1}}
                $customer->customer_id ?? $customer->id,               // {{2}}
                $billing->billing_number ?? $order->order_id,          // {{3}}
                $this->resolvePaymentLabel($order),                     // {{4}} label (e.g CIMB VA / E-Wallet / Alfamart)
                $this->resolvePaymentCode($order),                      // {{5}} VA number / ref number / provider
                'Rp ' . number_format($billing->total_amount, 0, ',', '.'), // {{6}}
                $expiredAt,                                             // {{7}}
            ];

            // === 1. WA notification ===
            if ($gateway->type === 'cloud') {
                // $this->cloudAPIService->sendTemplateMessage(
                //     account: $account,
                //     templateName: $templateName,
                //     phoneNumberId: $number->meta_phone_number_id,
                //     to: $to,
                //     bodyParams: $templateParams,
                //     buttonParams: [],
                //     traceId: $traceId
                // );
            } else {
                // Fallback GOWA
                $this->gowaService->orderCreated(
                    billingId: $billing->id,
                    orderId: $order->id,
                    action: WhatsAppAction::ORDER_CREATED->value,
                    eventId: $eventId,
                    deviceId: $number->device_id, // wajib untuk Gowa
                    traceId: $traceId
                );
            }

            // === Log success ===
            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'billing_id'  => $billing->id,
                    'order_id'    => $order->id,
                    'paymentType' => $paymentType,
                    'template'    => $templateName,
                    'gateway'     => $gateway->type,
                ],
                200,
                'Order-created notification dispatched successfully',
                $traceId
            );
        } catch (\Throwable $e) {

            LogHelper::logError(
                $e,
                [
                    'customer_id' => $customer->id,
                    'billing_id'  => $billing->id,
                    'order_id'    => $order->id,
                    'error'       => $e->getMessage(),
                ],
                500,
                'Failed to send order-created notification',
                $traceId
            );
        }
    }

    /**
     * Resolve template field {{4}} — label metode pembayaran
     */
    private function resolvePaymentLabel(Order $order): string
    {
        return match ($order->payment_type) {
            'E-Wallet'          => $order->payment_type,
            'Convenience Store' => $order->payment_method ?? 'Convenience Store',
            'Virtual Account'   => $order->payment_method . ' Virtual Account',
            default             => $order->payment_type,
        };
    }

    /**
     * Resolve template field {{5}} — number / reference / provider
     */
    private function resolvePaymentCode(Order $order): string
    {
        return match ($order->payment_type) {
            'E-Wallet'          => $order->payment_method, // e.g DANA, OVO, ShopeePay
            'Convenience Store' => $order->va_number, // ref number
            'Virtual Account'   => $order->va_number,
            default             => '',
        };
    }
}
