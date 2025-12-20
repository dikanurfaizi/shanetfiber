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
        private string $type,  // 'warning_bill' | 'deadline_bill' | 'reminder_bill'
        private MessageBuilder $builder,
        private Closure $getBankInfo,
        private Closure $getBillingPackages,
        private Closure $formatPackageInfo,
        private Closure $getUnpaidSummary,
        string $category
    ) {
        $this->setCategory($category);
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
        $admin = $cust->user;
        $bank = ($this->getBankInfo)($admin->id ?? 1);
        $packages = ($this->getBillingPackages)($cust->id);
        $pkgText = $packages->map(fn($p) => ($this->formatPackageInfo)($p))->implode("\n---\n");

        // Tentukan due date per billing (PL → pay_later, BL → due_date)
        $dueDates = $packages->map(function ($p) {
            return $p->status === 'PL'
                ? $p->pay_later
                : $p->due_date;
        });

        // Ambil due date paling dekat
        $due = $dueDates->filter()->sort()->first();

        // Hitung selisih hari dari hari ini
        $day = $due ? now()->diffInDays(Carbon::parse($due), false) : 0;

        // Format tanggal untuk template
        $due_date_formatted = $due
            ? Carbon::parse($due)->translatedFormat('d F Y')
            : '-';

        $unpaidSummary = ($this->getUnpaidSummary)($cust->id);

        return [
            'template_slug' => $this->type,
            'to'            => $cust->contact,
            'customer_id'   => $cust->id,
            'replacements'  => [
                '%name%'            => $cust->name,
                '%address%'         => $cust->address,
                '%customer_id%'     => $cust->customer_id,
                '%account_bank%'    => $bank,
                '%packages%'        => $pkgText,
                '%total-bill%'      => $this->builder->currency($unpaidSummary['total_unpaid']),
                '%due-date%'        => $due_date_formatted,
                '%day%'             => $day,
                '%cs_contact%'      => $admin?->contact ?? '-',
                '%count-bill%'      => $unpaidSummary['count_unpaid'],
            ],
        ];
    }
}
