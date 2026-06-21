<?php

namespace App\Services\Messaging\Handlers;

use App\Models\Billings\Billing;
use App\Models\Customers\Customer;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

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

        // Parameter getBankInfo membutuhkan int $userId (dari $adminId)
        $bankInfo = $adminId ? ($this->getBankInfo)($adminId) : 'Hubungi admin untuk info rekening';

        // --- Case 1: Ada tagihan BL (Belum Bayar) atau PL (Paylater) ---
        $activeBillings = $this->getActiveBillings($cust);

        if ($activeBillings->isNotEmpty()) {
            return $this->buildActiveBillPayload($cust, $cs, $activeBillings, $bankInfo);
        }

        // --- 🌟 Case 3 (PILIHAN UTAMA KEDUA): Deteksi Ketat Pelanggan Baru Pasang ---
        // Jika diaktivasi pada bulan berjalan INI dan total baris billing di DB baru ada 1 baris
        if ($this->isNewActivationThisMonth($cust) && $this->getBillingCount($cust) === 1) {
            return $this->buildNoBillPayload($cust, $cs);
        }

        // --- Case 2: Pelanggan lama yang sedang tidak ada tunggakan, cek tagihan LUNAS (LS) terakhir ---
        $lastPaid = $this->getLastPaidBilling($cust);

        if ($lastPaid) {
            return $this->buildPaidInfoPayload($lastPaid, $cust, $cs);
        }

        // --- Fallback Last Resort: Jika data kosong total atau tidak memenuhi syarat di atas ---
        return $this->buildNoBillPayload($cust, $cs);
    }

    /* ----------------------------------------------------------
     |  Helper Tambahan Khusus untuk Case 3 (Presisi & Ringan)
     * -------------------------------------------------------- */

    /**
     * Cek apakah activation_date customer berada di bulan dan tahun yang sama dengan sekarang
     */
    private function isNewActivationThisMonth(Customer $cust): bool
    {
        if (!$cust->activation_date) {
            return false;
        }

        $activation = Carbon::parse($cust->activation_date);
        $now = Carbon::now();

        return $activation->isSameMonth($now) && $activation->isSameYear($now);
    }

    /**
     * Hitung total baris data yang ada di tabel billing untuk customer ini (apapun statusnya)
     */
    private function getBillingCount(Customer $cust): int
    {
        return $cust->billing()->count(); // Menggunakan kueri COUNT() murni, sangat cepat dan hemat RAM
    }

    /* ----------------------------------------------------------
     |  Helpers / Data Fetchers (Optimized with Eager Loading)
     * -------------------------------------------------------- */

    private function getActiveBillings(Customer $cust): Collection
    {
        return $cust->billing()
            ->with(['billingSubscription.billingPeriode', 'customer.activeSubscription.package']) // 👈 Mencegah N+1 Query
            ->whereIn('status', ['BL', 'PL'])
            ->orderBy('due_date', 'asc') // 👈 Urutkan dari yang paling mendesak (paling lama jatuh temponya)
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
            'template_slug' => 'no_bill', // 👈 Mengarah ke template ramah baru
            'to'            => $cust->contact,
            'replacements'  => [
                '%name%'       => $cust->name,
                '%cs_contact%' => $cs,
            ],
        ];
    }

    // ✅ PERBAIKAN: Mengubah type hint dari "Billing" menjadi "Collection" agar tidak crash
    private function buildActiveBillPayload(Customer $cust, string $cs, Collection $billings, string $bankInfo): array
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
                "• Batas Pembayaran: *{$deadline}*"; // 👈 Di-bold agar orang tua mudah membacanya
        })->implode("\n\n---\n\n"); // Diberi separator pembatas antar lembar tagihan jika ada > 1 tagihan aktif
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
            'template_slug' => 'info_lunas', // 👈 Mengarah ke template lunas yang sopan
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

    private function paymentDeadline($billing): string
    {
        $date = $billing->status === 'PL'
            ? $billing->paylater_until
            : $billing->due_date;

        return $date
            ? Carbon::parse($date)->locale('id')->translatedFormat('d F Y')
            : 'Tidak ada batas waktu';
    }
}
