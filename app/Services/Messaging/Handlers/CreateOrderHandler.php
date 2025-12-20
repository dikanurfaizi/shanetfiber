<?php
// app/Services/Messaging/Handlers/CreateOrderHandler.php
namespace App\Services\Messaging\Handlers;

use App\Models\Billings\Billing;
use App\Models\Order;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use Carbon\Carbon;
use App\Helpers\LogHelper;

class CreateOrderHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private Billing $billing,
        private Order $order,
        private MessageBuilder $builder,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($billing->customer->contact);
    }

    public function action(): string
    {
        return 'order_created';
    }

    public function validate(string $traceId): void
    {
        $this->validateBase($traceId);
    }

    public function build(string $traceId): array
    {
        $cust = $this->billing->customer;
        $method = strtolower(str_replace(['-', ' '], '', $this->order->payment_type));
        $formattedAmount = $this->builder->currency($this->order->total_amount);
        $expiredTime = Carbon::createFromTimestamp($this->order->expired_time)
            ->locale('id')->translatedFormat('d F Y H:i');

        $paymentDetails = match ($method) {
            'virtualaccount'   => "- Metode: {$this->order->payment_name} {$this->order->va_number}\n- Nominal: {$formattedAmount}\n- Expired: {$expiredTime} WIB",
            'ewallet'          => "- Metode: E-Wallet {$this->order->payment_name}\n- Nominal: {$formattedAmount}\n- Expired: {$expiredTime} WIB",
            'conveniencestore' => "- Metode: Gerai {$this->order->payment_name}\n- Kode: {$this->order->va_number}\n- Nominal: {$formattedAmount}\n- Expired: {$expiredTime} WIB",
            default            => "- Metode: Menunggu konfirmasi\n- Nominal: {$formattedAmount}\n- Expired: {$expiredTime} WIB",
        };

        return [
            'template_slug' => 'order_created',
            'to'            => $cust->contact,
            'customer_id'   => $cust->id,
            'replacements'  => [
                '%name%'             => $cust->name,
                '%customer_id%'      => $cust->customer_id,
                '%billing_number%'   => $this->billing->billing_number,
                '%package%'          => $cust->package->name ?? '-',
                '%admin%'            => $cust->user?->name ?? '-',
                '%cs_contact%'       => $cust->user?->contact ?? '-',
                '%payment_details%'  => $paymentDetails,
            ],
        ];
    }
}
