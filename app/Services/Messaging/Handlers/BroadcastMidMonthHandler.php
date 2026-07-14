<?php
// app/Services/Messaging/Handlers/BroadcastMidMonthHandler.php
namespace App\Services\Messaging\Handlers;

use App\Models\Billings\Billing;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use Closure;
use Carbon\Carbon;
use App\Helpers\LogHelper;

class BroadcastMidMonthHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private Billing $billing,
        private string $type,  // 'billing_overdue' | 'billing_due_today' | 'billing_due_soon'
        private MessageBuilder $builder,
        private Closure $getBankInfo,
        private Closure $getBillingPackages,
        private Closure $formatPackageInfo,
        private Closure $getUnpaidSummary,
        string $category
    ) {
        $this->setCategory($category);
        // Menggunakan null-safe operator untuk menghindari error jika customer kosong
        $this->setRawContact($billing->customer?->contact ?? null);
    }

    public function action(): string
    {
        return $this->type;
    }

    public function validate(string $traceId): void
    {
        $this->validateBase($traceId);
    }

    public function build(string $traceId): array
    {
        $cust = $this->billing->customer;
        $admin = $cust?->user;

        // 1. Jalankan closure untuk mendapatkan bankPayload utuh
        $bankPayload = ($this->getBankInfo)();

        // Ambil teks list rekening terformat yang dibuat oleh trait MapsBroadcastBankPayload
        $bankText = $bankPayload['account_list'] ?? '-';

        // 2. Jalankan closure untuk mendapatkan fake collection paket
        $packages = ($this->getBillingPackages)();

        // Memformat informasi detail paket menggunakan FormatterCollector
        $pkgText = $packages->map(fn($p) => ($this->formatPackageInfo)($p))->implode("\n---\n");

        // 👉 PENYESUAIAN 1: Pastikan menggunakan paylater_until sesuai nama properti di fake object
        $dueDates = $packages->map(function ($p) {
            return $p->status === 'PL'
                ? $p->paylater_until
                : $p->due_date;
        });

        // Ambil due date paling dekat
        $due = $dueDates->filter()->sort()->first();

        // Hitung selisih hari dari hari ini
        $day = $due
            ? now()->startOfDay()->diffInDays(Carbon::parse($due)->startOfDay(), false)
            : 0;

        // Format tanggal untuk template
        $due_date_formatted = $due
            ? Carbon::parse($due)->translatedFormat('d F Y')
            : '-';

        LogHelper::logInfo(null, [
            'due_date' => $due,
            'due_date_formatted' => $due_date_formatted,
            'day_difference' => $day,
        ], 200, 'Mid-month broadcast build data', $traceId);

        // 👉 PENYESUAIAN 2: Hapus ($cust->id) karena closure ini sekarang instant return array statis tanpa query database
        $unpaidSummary = ($this->getUnpaidSummary)();

        return [
            'template_slug' => $this->type,
            'to'            => $cust->contact,
            'customer_id'   => $cust->id,
            'replacements'  => [
                '%name%'            => $cust->name,
                '%address%'         => $cust->address,
                '%customer_id%'     => $cust->customer_id,
                '%account_bank%'    => $bankText,
                '%packages%'        => $pkgText,
                '%total_bill%'      => $this->builder->currency($unpaidSummary['total_unpaid'] ?? 0),
                '%due_date%'        => $due_date_formatted,
                '%day%'             => $day,
                '%cs_contact%'      => $admin?->contact ?? '-',
                '%count_bill%'      => $unpaidSummary['count_unpaid'] ?? 0,
            ],
        ];
    }
}
