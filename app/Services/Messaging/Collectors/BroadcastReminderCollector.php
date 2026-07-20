<?php

namespace App\Services\Messaging\Collectors;

use App\Models\Billings\Billing;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BroadcastReminderCollector
{
    public function collect(Carbon $today): Collection
    {
        $todayStr = $today->toDateString();
        $firstDayOfMonth = $today->copy()->startOfMonth()->toDateString();
        $lastDayOfMonth  = $today->copy()->endOfMonth()->toDateString();

        return Billing::query()
            ->select([
                'billings.customer_id',
                DB::raw('MAX(billings.id) as id'),
                DB::raw('MAX(billings.status) as status'),
                DB::raw('MAX(billings.due_date) as due_date'),
                DB::raw('MAX(billings.paylater_until) as paylater_until'),
                DB::raw('MAX(billings.base_price) as base_price'),
                DB::raw('MAX(billings.discount_amount) as discount_amount'),
                DB::raw('MAX(billings.total_amount) as total_amount'),
            ])
            // Subquery hitung lembar tunggakan
            ->selectRaw("
                (SELECT COUNT(*) FROM billings as sub_b 
                 WHERE sub_b.customer_id = billings.customer_id 
                   AND sub_b.status IN ('BL', 'PL')) as count_unpaid
            ")
            // Subquery hitung total rupiah tunggakan
            ->selectRaw("
                (SELECT COALESCE(SUM(sub_b.total_amount), 0) FROM billings as sub_b 
                 WHERE sub_b.customer_id = billings.customer_id 
                   AND sub_b.status IN ('BL', 'PL')) as total_unpaid
            ")
            // 💡 PERBAIKAN 1: Bungkus dulu ekspresi kondisional ke dalam MAX() agar mematuhi aturan GROUP BY
            ->selectRaw("
                MAX(CASE 
                    WHEN DATE(CASE WHEN billings.status = 'BL' THEN billings.due_date ELSE billings.paylater_until END) < ? THEN 'billing_overdue'
                    WHEN DATE(CASE WHEN billings.status = 'BL' THEN billings.due_date ELSE billings.paylater_until END) = ? THEN 'billing_due_today'
                    ELSE 'billing_due_soon'
                END) as dynamic_type
            ", [$todayStr, $todayStr])
            ->with([
                'customer.user',
                'customer.activeSubscription.package',
                'customer.activeMembership.membership',
                'billingSubscription.billingPeriode',
            ])
            ->join('customers', 'billings.customer_id', '=', 'customers.id')

            // 👉 Blokir duplikasi kirim menggunakan WHERE NOT EXISTS terisolasi
            ->whereNotExists(function ($query) use ($todayStr, $firstDayOfMonth, $lastDayOfMonth) {
                $query->select(DB::raw(1))
                    ->from('message_events as me')
                    ->whereColumn('me.customer_id', 'billings.customer_id')
                    // 💡 Memastikan status pengecekan hanya untuk yang sukses terkirim atau proses pending
                    ->whereIn('me.status', ['success', 'pending'])
                    ->whereBetween('me.created_at', ["{$firstDayOfMonth} 00:00:00", "{$lastDayOfMonth} 23:59:59"])
                    ->whereRaw("
                        me.message_type = CASE 
                            WHEN DATE(CASE WHEN billings.status = 'BL' THEN billings.due_date ELSE billings.paylater_until END) < '{$todayStr}' THEN 'billing_overdue'
                            WHEN DATE(CASE WHEN billings.status = 'BL' THEN billings.due_date ELSE billings.paylater_until END) = '{$todayStr}' THEN 'billing_due_today'
                            ELSE 'billing_due_soon'
                        END
                    ");
            })
            ->whereIn('billings.status', ['BL', 'PL'])
            ->whereNotNull(DB::raw('COALESCE(billings.due_date, billings.paylater_until)'))
            ->whereNotNull('customers.contact')
            ->where('customers.is_opt_out', 0)
            ->groupBy('billings.customer_id')
            ->limit(15) // Limit harian langsung dikunci di query
            ->get();
    }
}
