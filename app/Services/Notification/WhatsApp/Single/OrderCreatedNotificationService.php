<?php

namespace App\Services\Notification\WhatsApp\Single;

use App\Enums\WhatsAppAction;
use App\Helpers\DummyReceiptHelper;
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\Order;
use App\Models\Customers\Customer;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Handlers\CreateOrderHandler;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use App\Services\WhatsApp\WhatsAppGatewayResolver;
use Carbon\Carbon;
use Throwable;

class OrderCreatedNotificationService extends AbstractSingleNotificationService
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
        Customer $customer,
        Billing $billing,
        Order $order,
        ?int $eventId = null,
        string $traceId = ''
    ): void {
        $timer = new ExecutionTimer();

        try {
            // 1. Resolve Gateway Context dari Base Class
            $context = $this->resolveGatewayContext('pembayaran_online', $traceId, $timer);
            if (!$context) return;

            $gateway = $context['gateway'];
            $account = $context['account'];

            if (empty($customer->contact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customer->id,
                ], 400, 'Customer has no contact for order-created notification.', $traceId, $timer->lap());
                return;
            }

            // Load missing relations if needed
            $customer->loadMissing(['package', 'user']);

            // 2. Prepare Data Secara Terpusat
            $orderData = $this->prepareOrderData($customer, $billing, $order);

            if ($gateway->type === 'cloud') {
                $templateName = match ($order->payment_type) {
                    'E-Wallet'          => 'pembayaran_dompet_digital',
                    'Convenience Store' => 'pembayaran_offline',
                    'Virtual Account'   => 'pembayaran_virtual_account',
                    default             => 'pembayaran_virtual_account',
                };

                $data = [
                    'recipient'      => $orderData['target_contact'],
                    'customer_id'    => $orderData['customer_code'],
                    'customer_name'  => $customer->name,
                    'billing_number' => $billing->billing_number ?? $order->order_id,
                    'payment_label'  => $this->resolvePaymentLabel($order),
                    'payment_code'   => $this->resolvePaymentCode($order),
                    'total_amount'   => 'Rp ' . number_format($billing->total_amount, 0, ',', '.'),
                    'expired_at'     => $orderData['expired_at_formatted'],
                    'account'        => $account,
                    'template_name'  => $templateName,
                    'event_id'       => $eventId,
                ];

                // $this->templateMessageService->sendOrderCreated($data);
            } else {
                // Direct Dispatch Handler ke Local Engine GOWA
                $this->dispatcher->dispatch(
                    action: $action->value,
                    handlerFactory: fn(string $category) => new CreateOrderHandler(
                        action: $action,
                        builder: $this->builder,
                        orderData: $orderData,
                        category: $category
                    ),
                    traceId: $traceId,
                    eventId: $eventId,
                    deviceId: $context['deviceId']
                );
            }

            // === Log success ===
            LogHelper::logInfo(null, [
                'customer_id'  => $customer->id,
                'billing_id'   => $billing->id,
                'order_id'     => $order->id,
                'payment_type' => $order->payment_type,
                'gateway_type' => $gateway->type,
            ], 200, 'Order-created notification dispatched successfully.', $traceId, $timer->lap());
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'customer_id'     => $customer->id,
                'billing_id'      => $billing->id ?? null,
                'order_id'        => $order->id ?? null,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, 'Failed to send order-created notification.', $traceId, $timer->lap());
        }
    }

    /**
     * Menyiapkan array data terstruktur dan menyusun detail pembayaran.
     */
    private function prepareOrderData(Customer $customer, Billing $billing, Order $order): array
    {
        $to = $this->formatNumber->formatReceiver($customer->contact, null, false);
        $method = strtolower(str_replace(['-', ' '], '', $order->payment_type));
        $formattedAmount = $this->builder->currency($order->total_amount);

        $expiredTime = Carbon::createFromTimestamp((int) $order->expired_time)
            ->timezone('Asia/Jakarta')
            ->locale('id')
            ->translatedFormat('d F Y, H:i');

        $paymentDetails = match ($method) {
            'virtualaccount'   => "- Metode: {$order->payment_name} {$order->va_number}\n- Nominal: {$formattedAmount}\n- Expired: {$expiredTime} WIB",
            'ewallet'          => "- Metode: E-Wallet {$order->payment_name}\n- Nominal: {$formattedAmount}\n- Expired: {$expiredTime} WIB",
            'conveniencestore' => "- Metode: Gerai {$order->payment_name}\n- Kode: {$order->va_number}\n- Nominal: {$formattedAmount}\n- Expired: {$expiredTime} WIB",
            default            => "- Metode: Menunggu konfirmasi\n- Nominal: {$formattedAmount}\n- Expired: {$expiredTime} WIB",
        };

        return [
            'order_id'             => $order->id,
            'billing_id'           => $billing->id,
            'customer_id'          => $customer->id,
            'customer_code'        => $customer->customer_id ?? $customer->id,
            'customer_name'        => $customer->name ?? '-',
            'target_contact'       => $to,
            'billing_number'       => $billing->billing_number ?? $order->order_id,
            'package_name'         => $customer->package->name ?? '-',
            'admin_name'           => $customer->user?->name ?? '-',
            'admin_contact'        => $customer->user?->contact ?? '-',
            'payment_details'      => $paymentDetails,
            'expired_at_formatted' => $expiredTime,
        ];
    }

    /**
     * Resolve template field — label metode pembayaran
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
     * Resolve template field — number / reference / provider
     */
    private function resolvePaymentCode(Order $order): string
    {
        return match ($order->payment_type) {
            'E-Wallet'          => $order->payment_method,
            'Convenience Store' => $order->va_number,
            'Virtual Account'   => $order->va_number,
            default             => '',
        };
    }
}
