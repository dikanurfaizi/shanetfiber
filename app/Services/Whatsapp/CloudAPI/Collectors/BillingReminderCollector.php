<?php

namespace App\Services\Whatsapp\CloudAPI\Collectors;

use App\Helpers\ExecutionTimer;
use App\Models\Billings\Billing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class BillingReminderCollector
{
    /**
     * Mengumpulkan data billing berdasarkan 3 slug template anti-duplikasi hari ini (Tanpa Limit).
     */
    public function collect(Carbon $today, string $slug1, string $slug2, string $slug3): Collection
    {
        $timer = new ExecutionTimer();
        $slugs = [$slug1, $slug2, $slug3];
        $todayStr = $today->toDateString();

        // 💡 SOLUSI DATA UNSTABLE: 
        // Batas Awal  : 01-Bulan-Ini 00:00:00 (Inclusive >=)
        // Batas Akhir : 16-Bulan-Ini 00:00:00 (Exclusive <)
        $startRange = $today->copy()->startOfMonth()->startOfDay()->toDateTimeString(); // e.g. '2026-08-01 00:00:00'
        $endRange   = $today->copy()->day(16)->startOfDay()->toDateTimeString();         // e.g. '2026-08-16 00:00:00'

        $query = Billing::query()
            ->select([
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.user_id as user_id',
                'p.name as package_name',

                // SUM total tagihan
                DB::raw('SUM(billings.total_amount) as total'),

                // MAX tanggal jatuh tempo
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

            // WHERE NOT EXISTS anti-duplikasi
            ->whereNotExists(function ($q) use ($slugs, $todayStr) {
                $q->select(DB::raw(1))
                    ->from('message_events as me')
                    ->whereColumn('me.customer_id', 'billings.customer_id')
                    ->whereIn('me.message_type', $slugs)
                    ->whereDate('me.created_at', $todayStr)
                    ->whereIn('me.status', ['success', 'failed', 'pending']);
            })

            // 💡 FILTER RENTANG TANGGAL DENGAN HALO/HALF-OPEN INTERVAL
            ->where(function ($q) use ($startRange, $endRange) {
                // Kasus BL (Belum Lunas)
                $q->where(function ($subQ) use ($startRange, $endRange) {
                    $subQ->where('billings.status', 'BL')
                        ->where('billings.due_date', '>=', $startRange)
                        ->where('billings.due_date', '<', $endRange);
                })
                    // Kasus PL (Paylater)
                    ->orWhere(function ($subQ) use ($startRange, $endRange) {
                        $subQ->where('billings.status', 'PL')
                            ->where('billings.paylater_until', '>=', $startRange)
                            ->where('billings.paylater_until', '<', $endRange);
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
                'date'         => $todayStr,
                'slugs'        => $slugs,
                'start_range'  => $startRange,
                'end_range'    => $endRange,
                'count'        => $data->count(),
                'execution_ms' => $timer->lap()
            ],
            120,
            '[BillingReminderCollector] Result collected successfully with half-open interval strategy'
        );

        return $data;
    }
}
