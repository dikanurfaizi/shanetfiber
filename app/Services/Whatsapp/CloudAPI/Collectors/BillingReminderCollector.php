<?php

namespace App\Services\WhatsApp\CloudAPI\Collectors;

use App\Helpers\ExecutionTimer;
use App\Models\Billings\Billing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class BillingReminderCollector
{
    /**
     * Mengumpulkan data billing berdasarkan slug template anti-duplikasi hari ini (Dinamis / Tanpa Limit).
     * 
     * @param Carbon $today Tanggal eksekusi
     * @param string ...$slugs Berapapun jumlah template slug yang ingin dicek
     */
    public function collect(Carbon $today, string ...$slugs): Collection
    {
        $timer    = new ExecutionTimer();
        $todayStr = $today->toDateString();

        // 1. RENTANG FILTER TAGIHAN (01 s/d 16 jam 00:00:00)
        $billingStart = $today->copy()->startOfMonth()->startOfDay()->toDateTimeString();
        $billingEnd   = $today->copy()->day(16)->startOfDay()->toDateTimeString();

        // 2. RENTANG ANTI-DUPLIKASI PESAN (HARI INI SAJA)
        $todayStart   = $today->copy()->startOfDay()->toDateTimeString();
        $todayEnd     = $today->copy()->endOfDay()->toDateTimeString();

        $query = Billing::query()
            ->select([
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.user_id as user_id',
                'p.name as package_name',

                DB::raw('SUM(billings.total_amount) as total'),
                DB::raw('MAX(billings.due_date) as due_date'),
                DB::raw('MAX(billings.paylater_until) as paylater_until'),
                DB::raw('MAX(COALESCE(billings.paylater_until, billings.due_date)) as final_due_date'),
            ])
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.user_id', '=', 'users.id')

            // Status subscription ACTIVE
            ->leftJoin('customer_subscriptions as cs', function ($join) {
                $join->on('customers.id', '=', 'cs.customer_id')
                    ->where('cs.status', '=', 'active');
            })
            ->leftJoin('packages as p', 'cs.package_id', '=', 'p.id')

            // ANTI-DUPLIKASI: Menggunakan array $slugs dinamis
            ->whereNotExists(function ($q) use ($slugs, $todayStart, $todayEnd) {
                $q->select(DB::raw(1))
                    ->from('message_events as me')
                    ->whereColumn('me.customer_id', 'billings.customer_id')
                    ->whereIn('me.message_type', $slugs)
                    ->whereBetween('me.created_at', [$todayStart, $todayEnd])
                    ->whereIn('me.status', ['success', 'failed', 'pending']);
            })

            // FILTER RENTANG TAGIHAN JATUH TEMPO (01 s/d 16)
            ->where(function ($q) use ($billingStart, $billingEnd) {
                $q->where(function ($subQ) use ($billingStart, $billingEnd) {
                    $subQ->where('billings.status', 'BL')
                        ->where('billings.due_date', '>=', $billingStart)
                        ->where('billings.due_date', '<', $billingEnd);
                })
                    ->orWhere(function ($subQ) use ($billingStart, $billingEnd) {
                        $subQ->where('billings.status', 'PL')
                            ->where('billings.paylater_until', '>=', $billingStart)
                            ->where('billings.paylater_until', '<', $billingEnd);
                    });
            })

            // GROUP BY PER CUSTOMER & PAKET
            ->groupBy([
                'customers.id',
                'customers.name',
                'customers.contact',
                'customers.user_id',
                'p.name'
            ]);

        $data = $query->get();

        LogHelper::logInfo(
            null,
            [
                'date'          => $todayStr,
                'slugs'         => $slugs,
                'billing_start' => $billingStart,
                'billing_end'   => $billingEnd,
                'today_start'   => $todayStart,
                'today_end'     => $todayEnd,
                'count'         => $data->count(),
                'execution_ms'  => $timer->lap()
            ],
            120,
            '[BillingReminderCollector] Result collected successfully',
            $timer->lap()
        );

        return $data;
    }
}
