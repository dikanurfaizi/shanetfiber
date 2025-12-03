<?php

namespace App\Services\Messaging\Collectors;

use App\Models\Billing\Billing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class BillingEligibleCollector
{
    public function getEligibleBillings(Carbon $startDate, Carbon $endDate, int $billingPeriodeId, string $traceId): Collection
    {
        $query = Billing::query()
            ->select([
                'billings.id as billing_id',
                'billings.billing_number',
                'billings.customer_id',
                'customers.customer_id as customer_unique_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.address as customer_address',
                'customers.is_opt_out',
                'billings.due_date',
                'users.name as admin_name',
                'users.id as admin_id',
                'users.contact as admin_contact',
                DB::raw('COUNT(*) as total_bills'),
                DB::raw('SUM(billings.total_amount) as total_amount'),
            ])
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.user_id', '=', 'users.id')
            ->leftJoin('billing_subscription_periodes as bsp', 'billings.billing_subscription_id', '=', 'bsp.id')
            ->leftJoin('message_events as me', function ($join) use ($startDate, $endDate) {
                $join->on('billings.customer_id', '=', 'me.customer_id')
                    ->where('me.message_type', '=', 'create_new_bill')
                    ->whereBetween('me.created_at', [$startDate, $endDate])
                    ->whereIn('me.status', ['success', 'failed']);
            })
            ->whereNull('me.id')
            ->whereNotNull('customers.contact')
            ->where('customers.contact', '!=', '')
            ->where('customers.is_opt_out', 0)
            ->where('bsp.billing_periode_id', $billingPeriodeId)
            ->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->where('billings.status', 'BL')->whereNotNull('billings.due_date');
                })->orWhere(function ($q2) {
                    $q2->where('billings.status', 'PL')->whereNotNull('billings.pay_later');
                });
            })
            ->groupBy([
                'billings.id',
                'billings.billing_number',
                'billings.customer_id',
                'customers.customer_id',
                'customers.name',
                'customers.contact',
                'customers.address',
                'billings.due_date',
                'users.name',
                'users.id',
                'users.contact',
            ])
            ->orderBy('customers.name')
            ->limit(10);

        $result = $query->get();

        LogHelper::logInfo(null, [
            'periode_id' => $billingPeriodeId,
            'record_count' => $result->count(),
        ], 200, "Eligible billings collected for broadcast.", $traceId);

        return $result;
    }
}
