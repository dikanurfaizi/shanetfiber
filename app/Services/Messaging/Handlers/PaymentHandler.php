<?php
// app/Services/Messaging/Handlers/PaymentHandler.php
namespace App\Services\Messaging\Handlers;

use App\Models\Billing\Billing;
use App\Models\Receipt;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use App\Helpers\LogHelper;

class PaymentHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private Billing $billing,
        private ?Receipt $receipt,
        private string $channel, // 'transfer' | 'cash' | 'payment_gateway'
        private MessageBuilder $builder,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($billing->customer->contact);
    }

    public function action(): string
    {
        return 'payment';
    }

    public function validate(string $traceId): void
    {
        $this->validateBase($traceId);
    }

    public function build(string $traceId): array
    {
        $cust = $this->billing->customer;

        $paymentMethodText = match ($this->channel) {
            'transfer' => sprintf(
                "%s %s - %s - %s",
                $this->receipt?->payment_methode,
                $this->receipt?->bank_name,
                $this->receipt?->account_name,
                $this->receipt?->account_number
            ),
            'cash' => 'Cash',
            default => $this->receipt?->payment_methode ?? 'Payment Gateway'
        };

        return [
            'template_slug' => 'payment',
            'to' => $cust->contact,
            'customer_id' => $cust->id,
            'replacements' => [
                '%name%'            => $this->billing->customer_name,
                '%address%'         => $this->billing->customer_address,
                '%customer_id%'     => $cust->customer_id,
                '%billing_number%'  => $this->receipt?->number ?? $this->billing->billing_number,
                '%package%'         => $this->receipt?->package_name ?? ($cust->package->name ?? '-'),
                '%periode%'         => $this->billing->billingSubscription?->billing_periode,
                '%bill%'            => $this->builder->currency($this->receipt?->package_price ?? $this->billing->base_price),
                '%discount%'        => $this->builder->currency($this->receipt?->discount ?? 0),
                '%total%'           => $this->builder->currency($this->receipt?->price_after_discount ?? $this->billing->total_amount),
                '%member%'          => $this->receipt?->member_type ?? 'Non Member / Tidak Ada',
                '%payment_time%'    => $this->receipt?->payment_time ? now()->parse($this->receipt->payment_time)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB' : null,
                '%teller%'          => $this->receipt?->teller_name ?? $this->billing->teller_name,
                '%payment_methode%' => $paymentMethodText,
                '%cs_contact%'      => $cust->user?->contact,
            ]
        ];
    }
}
