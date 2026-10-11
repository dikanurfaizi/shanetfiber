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
        $todayStr        = $today->toDateString();
        $firstDayOfMonth = $today->copy()->startOfMonth()->toDateTimeString();
        $lastDayOfMonth  = $today->copy()->endOfMonth()->toDateTimeString();

        return Billing::query()
            ->select([
                'billings.customer_id',
                DB::raw('MAX(billings.id) as id'),
                DB::raw('MAX(billings.status) as status'),
                DB::raw('MAX(billings.due_date) as due_date'),
                DB::raw('MAX(billings.paylater_until) as paylater_until'),
                DB::raw('SUM(billings.total_amount) as total_unpaid'), // 💡 Direct SUM tanpa subquery ganda
                DB::raw('COUNT(billings.id) as count_unpaid'),
                DB::raw("
                    CASE 
                        WHEN DATE(COALESCE(MAX(billings.paylater_until), MAX(billings.due_date))) < '{$todayStr}' THEN 'billing_overdue'
                        WHEN DATE(COALESCE(MAX(billings.paylater_until), MAX(billings.due_date))) = '{$todayStr}' THEN 'billing_due_today'
                        ELSE 'billing_due_soon'
                    END as dynamic_type
                ")
            ])
            ->with([
                'customer.user',
                'customer.activeSubscription.package',
            ])
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->whereIn('billings.status', ['BL', 'PL'])
            ->where('customers.contact', '!=', '')
            ->whereNotNull('customers.contact')
            ->where('customers.is_active', true) // 💡 Diperbaiki: Menggunakan is_active
            ->whereNotExists(function ($query) use ($firstDayOfMonth, $lastDayOfMonth) {
                $query->select(DB::raw(1))
                    ->from('message_events as me')
                    ->whereColumn('me.customer_id', 'billings.customer_id')
                    ->whereIn('me.status', ['success', 'pending'])
                    ->whereBetween('me.created_at', [$firstDayOfMonth, $lastDayOfMonth])
                    ->whereIn('me.message_type', ['billing_overdue', 'billing_due_today', 'billing_due_soon']);
            })
            ->groupBy('billings.customer_id')
            ->limit(15)
            ->get();
    }
}
