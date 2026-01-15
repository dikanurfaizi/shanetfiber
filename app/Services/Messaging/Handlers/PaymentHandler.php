<?php
// app/Services/Messaging/Handlers/PaymentHandler.php

namespace App\Services\Messaging\Handlers;

use App\Models\Billings\Billing;
use App\Models\Receipt;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;

class PaymentHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private Billing $billing,
        private ?Receipt $receipt,
        private string $channel, // transfer | cash | payment_gateway
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

        if (! $this->receipt) {
            throw new \RuntimeException('Receipt is required for payment notification.');
        }
    }

    public function build(string $traceId): array
    {
        $this->receipt->loadMissing('items');
        $this->billing->loadMissing('billingSubscription.billingPeriode');

        $cust        = $this->billing->customer;
        $packageItem = $this->receipt->packageItem();
        $addons      = $this->receipt->addons();

        $paymentMethodText = match ($this->channel) {
            'transfer' => sprintf(
                '%s %s - %s - %s',
                $this->receipt->payment_methode,
                $this->receipt->bank_name,
                $this->receipt->account_name,
                $this->receipt->account_number
            ),
            'cash' => 'Cash',
            default => $this->receipt->payment_methode ?? 'Payment Gateway',
        };

        return [
            'template_slug' => 'payment',
            'to'            => $cust->contact,
            'customer_id'   => $cust->id,
            'replacements'  => [
                '%name%'            => $cust->name,
                '%address%'         => $cust->address,
                '%customer_id%'     => $cust->customer_id,
                '%billing_number%'  => $this->receipt->number,
                '%package%'         => $packageItem?->name ?? '-',
                '%periode%'         => $this->billing->billingSubscription?->billingPeriode?->name ?? '-',
                '%bill%'            => $this->builder->currency($packageItem?->unit_price ?? 0),
                '%addons_section%' => $this->builder->addonsSection($addons),
                '%member_discount_section%' => $this->builder->memberDiscountSection(
                    optional($cust->activeMembership?->membership)->name,
                    $this->billing->discount_amount
                ),
                '%total%'           => $this->builder->currency($this->receipt->price_after_discount),
                '%payment_time%'    => optional($this->receipt->payment_time)
                    ? now()->parse($this->receipt->payment_time)
                    ->setTimezone('Asia/Jakarta')
                    ->translatedFormat('d F Y, H:i') . ' WIB'
                    : '-',
                '%teller%'          => $this->receipt->teller_name,
                '%payment_methode%' => $paymentMethodText,
                '%cs_contact%'      => $cust->user?->contact ?? '-',
            ],
        ];
    }
}
