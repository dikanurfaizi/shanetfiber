<?php

namespace App\Services\Messaging\Collectors;

use App\Models\Billings\Billing;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BroadcastMidMonthCollector
{
    public function collect(Carbon $today): Collection
    {
        $todayStr = $today->toDateString();

        return Billing::query()
            ->select('billings.*')
            // 👉 SOLUSI AMAN: Mengikat parameter subquery langsung di tempatnya menggunakan selectRaw
            ->selectRaw("
                (SELECT COUNT(*) FROM billings as sub_b 
                 WHERE sub_b.customer_id = billings.customer_id 
                   AND sub_b.status IN ('BL', 'PL')) as count_unpaid
            ")
            ->selectRaw("
                (SELECT COALESCE(SUM(sub_b.total_amount), 0) FROM billings as sub_b 
                 WHERE sub_b.customer_id = billings.customer_id 
                   AND sub_b.status IN ('BL', 'PL')) as total_unpaid
            ")
            // Hitung tipe dinamis hari ini
            ->selectRaw("
                CASE 
                    WHEN DATE(CASE WHEN billings.status = 'BL' THEN billings.due_date ELSE billings.paylater_until END) < ? THEN 'billing_overdue'
                    WHEN DATE(CASE WHEN billings.status = 'BL' THEN billings.due_date ELSE billings.paylater_until END) = ? THEN 'billing_due_today'
                    ELSE 'billing_due_soon'
                END as dynamic_type
            ", [$todayStr, $todayStr])
            ->with([
                'customer.user',
                'customer.activeSubscription.package',
                'customer.activeMembership.membership',
                'billingSubscription.billingPeriode',
            ])
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->leftJoin('message_events as me', function ($join) use ($todayStr) {
                $join->on('billings.customer_id', '=', 'me.customer_id')
                    ->on('me.message_type', '=', DB::raw("
                        CASE 
                            WHEN DATE(CASE WHEN billings.status = 'BL' THEN due_date ELSE paylater_until END) < '{$todayStr}' THEN 'billing_overdue'
                            WHEN DATE(CASE WHEN billings.status = 'BL' THEN due_date ELSE paylater_until END) = '{$todayStr}' THEN 'billing_due_today'
                            ELSE 'billing_due_soon'
                        END
                    "))
                    ->whereDate('me.created_at', '=', $todayStr)
                    ->whereIn('me.status', ['success', 'failed']);
            })
            ->whereNull('me.id')
            ->whereIn('billings.status', ['BL', 'PL'])
            ->whereNotNull(DB::raw('COALESCE(billings.due_date, billings.paylater_until)'))
            ->whereNotNull('customers.contact')
            ->where('customers.is_opt_out', 0)
            ->limit(5)
            ->get();
    }
}
