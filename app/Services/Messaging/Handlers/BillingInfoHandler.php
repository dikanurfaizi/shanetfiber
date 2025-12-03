<?php

namespace App\Services\Messaging\Handlers;

use App\Models\Customer;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use Illuminate\Support\Carbon;

class BillingInfoHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private Customer $customer,
        private MessageBuilder $builder,
        private \Closure $getBankInfo,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($customer->contact);
    }

    public function action(): string
    {
        return 'info_tagihan';
    }

    public function validate(string $traceId): void
    {
        $this->validateBase($traceId);
    }

    public function build(string $traceId): array
    {
        $cust = $this->customer;
        $cs   = $cust?->user?->contact ?? '-';
        $adminId = $cust?->user?->id ?? null;
        $bankInfo = $adminId ? ($this->getBankInfo)($adminId) : '-';

        $activeBillings = $this->getActiveBillings($cust);

        // --- Case 1: Ada tagihan BL/PL ---
        if ($activeBillings->isNotEmpty()) {
            return $this->buildActiveBillPayload($cust, $cs, $activeBillings, $bankInfo);
        }

        // --- Case 2: Tidak ada BL/PL, cek tagihan LUNAS terakhir ---
        $lastPaid = $this->getLastPaidBilling($cust);

        if ($lastPaid) {
            return $this->buildPaidInfoPayload($lastPaid, $cust, $cs);
        }

        // --- Case 3: Tidak ada tagihan sama sekali ---
        return $this->buildNoBillPayload($cust, $cs);
    }

    /* ----------------------------------------------------------
     |  Helpers
     * -------------------------------------------------------- */

    private function getActiveBillings(Customer $cust)
    {
        return $cust->billing()
            ->whereIn('status', ['BL', 'PL'])
            ->orderBy('due_date', 'desc')
            ->get();
    }

    private function getLastPaidBilling(Customer $cust)
    {
        return $cust->billing()
            ->where('status', 'LS') // Lunas
            ->orderBy('payment_time', 'desc')
            ->first();
    }

    private function buildNoBillPayload(Customer $cust, string $cs): array
    {
        return [
            'template_slug' => 'no_bill',
            'to'            => $cust->contact,
            'replacements'  => [
                '%name%'       => $cust->name,
                '%cs_contact%' => $cs,
            ],
        ];
    }

    private function buildActiveBillPayload(Customer $cust, string $cs, $billings, $bankInfo): array
    {
        $list = $this->formatBillingList($billings);
        $total = $this->builder->currency($billings->sum('total_amount'));

        return [
            'template_slug' => 'info_tagihan',
            'to'            => $cust->contact,
            'replacements'  => [
                '%name%'         => $cust->name,
                '%bills%'        => $list,
                '%total-bills%'  => $total,
                '%account_bank%' => $bankInfo,
                '%cs_contact%'   => $cs,
            ],
        ];
    }

    private function formatBillingList($billings): string
    {
        return $billings->map(function ($b) {

            $periode  = $b->billingSubscription?->billing_periode ?? 'Periode';
            $package  = $b->customer->package->name;
            $harga    = $this->builder->currency($b->total_amount);
            $status   = $this->translateStatusWithDate($b);
            $deadline = $this->paymentDeadline($b);

            return
                "• Paket: {$package}\n" .
                "• Harga Paket: {$harga}\n" .
                "• Periode {$periode}\n" .
                "• Status: {$status}\n" .
                "• Batas Pembayaran: {$deadline}";
        })->implode("\n\n");
    }

    private function buildPaidInfoPayload($billing, Customer $cust, string $cs): array
    {
        return [
            'template_slug' => 'info_lunas',
            'to'            => $cust->contact,
            'replacements'  => [
                '%name%'         => $cust->name,
                '%package'       => $cust->package->name,
                '%periode%'      => $billing->billingSubscription?->billing_periode ?? '-',
                '%total%'        => $this->builder->currency($billing->total_amount),
                '%paid_at%'      => $billing->payment_time
                    ? Carbon::parse($billing->payment_time)->locale('id')->translatedFormat('d F Y')
                    : '-',
                '%method%'       => $billing->receipt->payment_methode,
                '%cs_contact%'   => $cs,
            ],
        ];
    }

    private function translateStatusWithDate($billing): string
    {
        $now = Carbon::now();

        if ($billing->status === 'PL') {
            if ($billing->pay_later && $now->gt(Carbon::parse($billing->pay_later))) {
                return 'Melewati Masa Bayar';
            }
            return 'Paylater';
        }

        if ($billing->status === 'BL') {
            if ($billing->due_date && $now->gt(Carbon::parse($billing->due_date))) {
                return 'Melewati Masa Bayar';
            }
            return 'Belum Dibayar';
        }

        return 'Tidak Diketahui';
    }

    private function paymentDeadline($billing): string
    {
        $date = $billing->status === 'PL'
            ? $billing->pay_later
            : $billing->due_date;

        return $date
            ? Carbon::parse($date)->locale('id')->translatedFormat('d F Y')
            : '-';
    }
}
