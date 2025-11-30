<?php
namespace App\Services\Messaging\Collectors;

use App\Models\Billing\Billing;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BroadcastMidMonthCollector
{
    public function collect(Carbon $today, string $type): Collection
    {
        $dateOps = match ($type) {
            'past_due'  => '<',
            'due_today' => '=',
            'upcoming'  => '>',
            default     => null,
        };

        if (!$dateOps) return collect();

        return Billing::query()
            ->select(
                'billings.id as billing_id',
                'billings.customer_id',
                'billings.billing_number',
                'billings.status',
                'billings.due_date',
                'customers.customer_id as customer_unique_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.address as customer_address',
                'users.id as admin_id',
                'users.name as admin_name',
                'users.contact as admin_contact'
            )
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.user_id', '=', 'users.id')
            ->whereIn('billings.status', ['BL', 'PL'])
            ->whereNotNull(DB::raw('COALESCE(billings.due_date, billings.pay_later)'))
            ->whereDate(DB::raw('COALESCE(billings.due_date, billings.pay_later)'), $dateOps, $today)
            ->limit(10)
            ->get();
    }
}
