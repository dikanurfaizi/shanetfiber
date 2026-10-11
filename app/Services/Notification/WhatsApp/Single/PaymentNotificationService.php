<?php

namespace App\Services\Notification\WhatsApp\Single;

use App\Enums\WhatsAppAction;
use App\Helpers\DummyReceiptHelper;
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Receipt;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Handlers\PaymentHandler;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use App\Services\WhatsApp\WhatsAppGatewayResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Throwable;

class PaymentNotificationService extends AbstractSingleNotificationService
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

    /**
     * Dipanggil dari Job: load receipt, lalu kirim WA.
     */
    public function send(WhatsAppAction $action, int $receiptId, string $traceId, ?int $eventId = null): void
    {
        $timer = new ExecutionTimer();

        $receipt = Receipt::with([
            'billingPayment.billing.customer',
        ])->find($receiptId);

        if (!$receipt) {
            LogHelper::logWarning(null, [
                'receipt_id' => $receiptId,
            ], 404, 'Receipt not found for WhatsApp notification.', $traceId, $timer->lap());

            return;
        }

        $this->sendWhatsAppPaymentNotification($action, $receipt, $traceId, $eventId);
    }

    /**
     * Core WA notification logic.
     */
    public function sendWhatsAppPaymentNotification(WhatsAppAction $action, Receipt $receipt, string $traceId, ?int $eventId): void
    {
        $timer = new ExecutionTimer();

        try {
            // 1. Resolve Gateway Context
            $context = $this->resolveGatewayContext($action->value, $traceId, $timer);
            if (!$context) return;

            $gateway = $context['gateway'];
            $account = $context['account'];

            $billing = $receipt->billingPayment?->billing;

            if (!$billing || !$billing->customer) {
                LogHelper::logError(null, [
                    'receipt_id' => $receipt->id,
                    'billing_id' => $billing?->id,
                ], 404, 'Billing or Customer relation not found for payment notification.', $traceId, $timer->lap());

                return;
            }

            if (empty($billing->customer->contact)) {
                LogHelper::logWarning(null, [
                    'receipt_id'  => $receipt->id,
                    'customer_id' => $billing->customer_id,
                ], 400, 'Customer has no contact for payment notification.', $traceId, $timer->lap());

                return;
            }

            // 2. Prepare Data Secara Terpusat
            $paymentData = $this->preparePaymentData($receipt, $billing);

            if ($gateway->type === 'cloud') {
                $recipient = $this->formatNumber->formatReceiver($paymentData['customer_contact'], null, false);

                $token = Crypt::encryptString($receipt->slug);
                $token = str_replace(['+', '/', '='], ['-', '_', ''], $token);

                $data = [
                    'recipient'     => $recipient,
                    'customer_id'   => $receipt->customer_id,
                    'customer_name' => $receipt->customer_name,
                    'period'        => $receipt->periode,
                    'amount'        => 'Rp ' . number_format($receipt->price_after_discount, 0, ',', '.'),
                    'payment_time'  => $paymentData['payment_time_formatted'],
                    'teller_name'   => $receipt->teller_name,
                    'receipt_code'  => $token,
                    'account'       => $account,
                    'event_id'      => $eventId,
                ];

                $this->templateMessageService->sendInfoPembayaran($data);
            } else {
                // Direct Dispatch Handler Local Engine
                $this->dispatcher->dispatch(
                    action: $action->value,
                    handlerFactory: fn(string $category) => new PaymentHandler(
                        action: $action,
                        builder: $this->builder,
                        paymentData: $paymentData,
                        category: $category
                    ),
                    traceId: $traceId,
                    eventId: $eventId,
                    deviceId: $context['deviceId']
                );
            }

            LogHelper::logInfo(null, [
                'receipt_id'   => $receipt->id,
                'billing_id'   => $billing->id,
                'customer_id'  => $billing->customer_id,
                'gateway_type' => $gateway->type,
            ], 200, 'Payment notification dispatched successfully.', $traceId, $timer->lap());
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'receipt_id'      => $receipt->id ?? null,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, 'Failed sending WhatsApp payment notification.', $traceId, $timer->lap());
        }
    }

    /**
     * Menyiapkan dan mengolah data kuitansi & tagihan secara terpusat.
     */
    private function preparePaymentData(Receipt $receipt, Billing $billing): array
    {
        $receipt->loadMissing('items');
        $billing->loadMissing(['customer.user', 'billingSubscription.billingPeriode']);

        $customer = $billing->customer;
        $packageItem = $receipt->packageItem();
        $addons = $receipt->addons();

        // Teks Metode Pembayaran
        $methodName = Str::headline($receipt->payment_methode ?? 'Payment Gateway');

        // 2. Jika transfer, gabungkan dengan detail bank
        if (strtolower($receipt->payment_methode) === 'transfer' && !empty($receipt->bank_name)) {
            $paymentMethodText = sprintf(
                '%s %s - %s - %s',
                $methodName,
                $receipt->bank_name,
                $receipt->account_name ?? '-',
                $receipt->account_number ?? '-'
            );
        } else {
            // Untuk cash, e-wallet, atau metode lainnya
            $paymentMethodText = $methodName;
        }

        // Format Waktu Pembayaran
        $paymentTimeFormatted = optional($receipt->payment_time)
            ? Carbon::parse($receipt->payment_time)
            ->setTimezone('Asia/Jakarta')
            ->locale('id')
            ->translatedFormat('d F Y, H:i') . ' WIB'
            : '-';

        return [
            'receipt_id'             => $receipt->id,
            'billing_id'             => $billing->id,
            'customer_id'            => $customer->id,
            'customer_code'          => $customer->customer_id ?? $customer->id,
            'customer_name'          => $customer->name ?? $receipt->customer_name ?? '-',
            'customer_address'       => $customer->address ?? '-',
            'customer_contact'       => $customer->contact ?? '',
            'receipt_number'         => $receipt->number,
            'package_name'           => $packageItem?->name ?? '-',
            'package_price'          => $packageItem?->unit_price ?? 0,
            'periode'                => $billing->billingSubscription?->billingPeriode?->name ?? $receipt->periode ?? '-',
            'addons'                 => $addons,
            'membership_name'        => optional($customer->activeMembership?->membership)->name,
            'discount_amount'        => $billing->discount_amount ?? 0,
            'total_price'            => $receipt->price_after_discount ?? 0,
            'payment_time_formatted' => $paymentTimeFormatted,
            'teller_name'            => $receipt->teller_name ?? 'System',
            'payment_method_text'    => $paymentMethodText,
            'cs_contact'             => $customer->user?->contact ?? '-',
        ];
    }
}
