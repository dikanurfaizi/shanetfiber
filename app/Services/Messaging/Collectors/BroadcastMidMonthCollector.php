<?php

namespace App\Services\Messaging\Collectors;

use App\Helpers\LogHelper;
use App\Models\Billing\Billing;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BroadcastMidMonthCollector
{
    public function collect(Carbon $today, string $type): Collection
    {
        $dateOps = match ($type) {
            'billing_overdue'  => '<',
            'billing_due_today' => '=',
            'billing_due_soon'  => '>',
            default     => '=',
        };

        $slug = match ($type) {
            'billing_overdue'  => 'billing_overdue',
            'billing_due_today' => 'billing_due_today',
            'billing_due_soon'  => 'billing_due_soon',
            default     => 'billing_due_today',
        };

        if (!$dateOps || !$slug) return collect();

        return Billing::query()
            ->select(
                'billings.id as billing_id',
                'billings.customer_id',
                'billings.billing_number',
                'billings.status',
                'billings.due_date',
                'billings.pay_later',
                'customers.customer_id as customer_unique_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.address as customer_address',
                'customers.is_opt_out',
                'users.id as admin_id',
                'users.name as admin_name',
                'users.contact as admin_contact'
            )
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.user_id', '=', 'users.id')
            ->leftJoin('message_events as me', function ($join) use ($today, $slug) {
                $join->on('billings.customer_id', '=', 'me.customer_id')
                    ->where('me.message_type', '=', $slug)
                    ->whereDate('me.created_at', '=', $today)
                    ->whereIn('me.status', ['success', 'failed']);
            })
            ->whereNull('me.id')
            ->whereIn('billings.status', ['BL', 'PL'])
            ->whereNotNull(DB::raw('COALESCE(billings.due_date, billings.pay_later)'))
            ->whereNotNull('customers.contact')
            ->whereDate(
                DB::raw("CASE WHEN billings.status = 'BL' THEN billings.due_date ELSE billings.pay_later END"),
                $dateOps,
                $today
            )
            ->where('customers.is_opt_out', 0)
            ->limit(5)
            ->get();
    }
}
