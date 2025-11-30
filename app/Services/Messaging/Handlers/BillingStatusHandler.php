<?php
// app/Services/Messaging/Handlers/BillingStatusHandler.php
namespace App\Services\Messaging\Handlers;

use App\Models\Billing\Billing;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use App\Helpers\LogHelper;
use Illuminate\Support\Carbon;

class BillingStatusHandler implements MessageActionHandler
{
    public function __construct(
        private Billing $billing,
        private string $type, // 'paylater' | 'unpayment'
        private MessageBuilder $builder,
        private \Closure $getBankInfo
    ) {}

    public function action(): string
    {
        return $this->type;
    }

    public function validate(string $traceId): void
    {
        if (empty($this->billing->customer?->contact)) {
            LogHelper::logError(null, [
                'billing_id' => $this->billing->id,
                'trace_id' => $traceId,
            ], 400, "Contact kosong saat validasi BillingStatusHandler", $traceId);
            throw new \RuntimeException('Contact kosong');
        }
    }

    public function build(string $traceId): array
    {
        $this->billing->loadMissing(['customer.user', 'billingSubscription']);

        $cust      = $this->billing->customer;
        $user      = $cust?->user;
        $periode   = $this->billing->billingSubscription?->billing_periode ?? '-';
        $bank      = ($this->getBankInfo)($user?->id ?? 1);

        return [
            'template_slug' => $this->type,
            'to'            => $cust->contact,
            'customer_id'   => $cust->id,
            'replacements'  => [
                '%name%'           => $cust->name,
                '%customer_id%'    => $cust->customer_id,
                '%billing_number%' => $this->billing->billing_number,
                '%package%'        => $cust->package->name ?? '-',
                '%periode%'        => $periode ?? '-',
                '%bill%'           => $this->builder->currency($this->billing->base_price),
                '%discount%'       => $this->builder->currency($this->billing->discount_amount ?? 0),
                '%total%'          => $this->builder->currency($this->billing->total_amount),
                '%account_bank%'   => $bank,
                '%admin%'          => $user?->name ?? '-',
                '%member%'         => optional($cust->member)->type ?? 'Non Member',
                '%cs_contact%'     => $user?->contact ?? '-',
                '%teller%'         => $this->billing->teller_name,
                '%paylater%'       => $this->billing->pay_later ? Carbon::parse($this->billing->pay_later)->locale('id')->translatedFormat('d F Y') : '-',
            ],
        ];
    }
}
