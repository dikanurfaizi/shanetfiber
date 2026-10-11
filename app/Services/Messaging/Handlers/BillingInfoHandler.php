<?php

namespace App\Services\Messaging\Handlers;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Customers\Customer;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Throwable;

class BillingInfoHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private Customer $customer,
        private MessageBuilder $builder,
        private string $adminBankInfo,
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
        $timer = new ExecutionTimer();
        $cust  = $this->customer;

        LogHelper::logInfo(
            null,
            [
                'handler'     => static::class,
                'action'      => $this->action(),
                'customer_id' => $cust->id,
            ],
            200,
            '[BillingInfoHandler] Building payload message for billing info',
            $traceId
        );

        try {
            $cs       = $cust->user?->contact ?? '-';
            $bankInfo = !empty($this->adminBankInfo) ? $this->adminBankInfo : 'Hubungi admin untuk info rekening';

            // --- Case 1: Ada tagihan BL (Belum Bayar) atau PL (Paylater) ---
            $activeBillings = $this->getActiveBillings($cust);

            if ($activeBillings->isNotEmpty()) {
                $payload = $this->buildActiveBillPayload($cust, $cs, $activeBillings, $bankInfo);
            }
            // --- Case 2: Deteksi Pelanggan Baru Pasang pada bulan berjalan ---
            elseif ($this->isNewActivationThisMonth($cust) && $this->getBillingCount($cust) === 1) {
                $payload = $this->buildNoBillPayload($cust, $cs);
            }
            // --- Case 3: Cek tagihan LUNAS (LS) terakhir ---
            elseif ($lastPaid = $this->getLastPaidBilling($cust)) {
                $payload = $this->buildPaidInfoPayload($lastPaid, $cust, $cs);
            }
            // --- Fallback Last Resort ---
            else {
                $payload = $this->buildNoBillPayload($cust, $cs);
            }

            LogHelper::logInfo(
                null,
                [
                    'handler'       => static::class,
                    'customer_id'   => $cust->id,
                    'template_slug' => $payload['template_slug'] ?? null,
                ],
                200,
                '[BillingInfoHandler] Payload successfully built',
                $traceId,
                $timer->lap()
            );

            return $payload;
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(
                null,
                [
                    'handler'         => static::class,
                    'customer_id'     => $cust->id,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[BillingInfoHandler] Unexpected error occurred while building payload',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }

    /* ----------------------------------------------------------
     |  Helpers & Data Fetchers
     * -------------------------------------------------------- */

    private function isNewActivationThisMonth(Customer $cust): bool
    {
        if (!$cust->activation_date) {
            return false;
        }

        $activation = Carbon::parse($cust->activation_date);
        $now        = Carbon::now();

        return $activation->isSameMonth($now) && $activation->isSameYear($now);
    }

    private function getBillingCount(Customer $cust): int
    {
        return $cust->billing()->count();
    }

    private function getActiveBillings(Customer $cust): Collection
    {
        return $cust->billing()
            ->with(['billingSubscription.billingPeriode', 'customer.activeSubscription.package'])
            ->whereIn('status', ['BL', 'PL'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    private function getLastPaidBilling(Customer $cust): ?Billing
    {
        return $cust->billing()
            ->with(['billingSubscription.billingPeriode', 'billingPayment.receipt'])
            ->where('status', 'LS')
            ->orderBy('created_at', 'desc')
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

    private function buildActiveBillPayload(Customer $cust, string $cs, Collection $billings, string $bankInfo): array
    {
        $list  = $this->formatBillingList($billings);
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

    private function formatBillingList(Collection $billings): string
    {
        return $billings->map(function ($b) {
            $periode  = $b->billingSubscription?->billingPeriode?->name ?? 'Periode Tidak Diketahui';
            $package  = $b->customer?->activeSubscription?->package?->name ?? 'Paket Shanet';
            $harga    = $this->builder->currency($b->total_amount);
            $status   = $this->translateStatusWithDate($b);
            $deadline = $this->paymentDeadline($b);

            return
                "• Nama Paket: {$package}\n" .
                "• Biaya Paket: {$harga}\n" .
                "• Periode Layanan: {$periode}\n" .
                "• Status Tagihan: *{$status}*\n" .
                "• Batas Pembayaran: *{$deadline}*";
        })->implode("\n\n---\n\n");
    }

    private function buildPaidInfoPayload(Billing $billing, Customer $cust, string $cs): array
    {
        $packageName = $cust->activeSubscription?->package?->name
            ?? $billing->customer?->activeSubscription?->package?->name
            ?? 'Paket Shanum';

        $paymentTime = $billing->billingPayment?->payment_time
            ? Carbon::parse($billing->billingPayment->payment_time)
            ->locale('id')
            ->translatedFormat('d F Y H:i') . ' WIB'
            : '-';

        $method = $billing->billingPayment?->receipt?->payment_methode
            ? ucwords(str_replace('_', ' ', $billing->billingPayment->receipt->payment_methode))
            : 'Transfer Bank';

        return [
            'template_slug' => 'info_lunas',
            'to'            => $cust->contact,
            'replacements'  => [
                '%name%'       => $cust->name,
                '%package%'    => $packageName,
                '%periode%'    => $billing->billingSubscription?->billingPeriode?->name ?? '-',
                '%total%'      => $this->builder->currency($billing->total_amount),
                '%paid_at%'    => $paymentTime,
                '%method%'     => $method,
                '%cs_contact%' => $cs,
            ],
        ];
    }

    private function translateStatusWithDate(Billing $billing): string
    {
        $now = Carbon::now();

        if ($billing->status === 'PL') {
            if ($billing->paylater_until && $now->gt(Carbon::parse($billing->paylater_until))) {
                return 'Melewati Batas Paylater (Menunggak)';
            }
            return 'Paylater';
        }

        if ($billing->status === 'BL') {
            if ($billing->due_date && $now->gt(Carbon::parse($billing->due_date))) {
                return 'Terlambat (Melewati Masa Bayar)';
            }
            return 'Belum Dibayar';
        }

        return 'Belum Terselesaikan';
    }

    private function paymentDeadline(Billing $billing): string
    {
        $date = $billing->status === 'PL'
            ? $billing->paylater_until
            : $billing->due_date;

        return $date
            ? Carbon::parse($date)->locale('id')->translatedFormat('d F Y')
            : 'Tidak ada batas waktu';
    }
}
