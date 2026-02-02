<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

class KpiReportService
{
    public function buildKpiByMikrotik(int $year): array
    {
        // ambil semua mikrotik yang punya customer
        $mikrotiks = DB::table('mikrotiks')
            ->select('id', 'name')
            ->where('disabled', 'false')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('customers')
                    ->whereColumn('customers.mikrotik_id', 'mikrotiks.id');
            })
            ->orderBy('name')
            ->get();

        $result = [];

        foreach ($mikrotiks as $m) {
            $result[] = [
                'mikrotik_id' => $m->id,
                'mikrotik_name' => $m->name,
                'months' => $this->buildMonthlyKpi($m->id, $year),
            ];
        }

        return $result;
    }

    protected function buildMonthlyKpi(int $mikrotikId, int $year): array
    {
        $periodes = DB::table('billing_periodes')
            ->whereYear('start_date', $year)
            ->orderBy('start_date')
            ->get();

        $rows = [];

        foreach ($periodes as $p) {
            // sementara expense dummy
            $month = date('m', strtotime($p->start_date));
            $year  = date('Y', strtotime($p->start_date));

            $expenses = DB::table('expenses as e')
                ->whereNull('e.deleted_at')
                ->whereMonth('e.expense_date', $month)
                ->whereYear('e.expense_date', $year)
                ->whereIn('e.user_id', function ($q) use ($mikrotikId) {
                    $q->select('user_id')
                        ->from('customers')
                        ->where('mikrotik_id', $mikrotikId)
                        ->distinct();
                })
                ->sum('e.amount');

            // Active customers 
            $custActive = DB::table('customer_subscriptions as cs')
                ->join('billing_subscription_periodes as bsp', 'bsp.customer_subscription_id', '=', 'cs.id')
                ->join('customers as c', 'c.id', '=', 'cs.customer_id')
                ->where('bsp.billing_periode_id', $p->id)
                ->where('cs.status', 'active')
                ->where('c.mikrotik_id', $mikrotikId)
                ->distinct('cs.customer_id')
                ->count('cs.customer_id');

            // Unsubscribed customers
            $unsubCust = DB::table('customer_subscriptions as cs')
                ->join('billing_subscription_periodes as bsp', 'bsp.customer_subscription_id', '=', 'cs.id')
                ->join('customers as c', 'c.id', '=', 'cs.customer_id')
                ->where('bsp.billing_periode_id', $p->id)
                ->whereIn('cs.status', ['terminated', 'paused'])
                ->where('c.mikrotik_id', $mikrotikId)
                ->distinct('cs.customer_id')
                ->count('cs.customer_id');

            $unsubNominal = DB::table('customer_subscriptions as cs')
                ->join('billing_subscription_periodes as bsp', 'bsp.customer_subscription_id', '=', 'cs.id')
                ->join('customers as c', 'c.id', '=', 'cs.customer_id')
                ->join('packages as p', 'p.id', '=', 'cs.package_id')
                ->join('billings as b', 'b.billing_subscription_id', '=', 'bsp.id')
                ->where('bsp.billing_periode_id', $p->id)
                ->whereIn('cs.status', ['terminated', 'paused'])
                ->where('c.mikrotik_id', $mikrotikId)
                ->sum('b.total_amount');

            $subscriptionNominal = DB::table('customer_subscriptions as cs')
                ->join('billing_subscription_periodes as bsp', 'bsp.customer_subscription_id', '=', 'cs.id')
                ->join('customers as c', 'c.id', '=', 'cs.customer_id')
                ->join('packages as p', 'p.id', '=', 'cs.package_id')
                ->join('billings as b', 'b.billing_subscription_id', '=', 'bsp.id')
                ->where('bsp.billing_periode_id', $p->id)
                ->where('cs.status', 'active')
                ->where('c.mikrotik_id', $mikrotikId)
                ->sum('b.total_amount');


            $billingAll = $this->billingAggregate($mikrotikId, $p->id);
            $payment    = $this->billingAggregate($mikrotikId, $p->id, 'LS');
            $unpayment  = $this->billingAggregate($mikrotikId, $p->id, 'BL');
            $paylater   = $this->billingAggregate($mikrotikId, $p->id, 'PL');

            $totalCustomers = $custActive + $unsubCust;

            $rows[] = [
                'label' => date('M Y', strtotime($p->start_date)),

                'cust_active' => $custActive,

                'billing' => [
                    'amount' => $billingAll['amount'],
                ],

                'payment' => $payment,
                'unpayment' => $unpayment,
                'paylater' => $paylater,

                'subscription' => [
                    'cust' => $custActive,
                    'nominal' => $subscriptionNominal, // kalau mau weighted nanti kita bahas
                ],

                'unsubscription' => [
                    'cust' => $unsubCust,
                    'nominal' => $unsubNominal,
                ],

                'expenses' => $expenses,

                'net_income' => $payment['amount'] - $expenses,

                'total_customers' => $totalCustomers,
                'total_billings' => $billingAll['amount'],
            ];
        }

        return $rows;
    }


    protected function billingAggregate(
        int $mikrotikId,
        int $billingPeriodeId,
        ?string $status = null
    ): array {
        $q = DB::table('billings as b')
            ->join('billing_subscription_periodes as bsp', 'b.billing_subscription_id', '=', 'bsp.id')
            ->join('customer_subscriptions as cs', 'bsp.customer_subscription_id', '=', 'cs.id')
            ->join('customers as c', 'c.id', '=', 'cs.customer_id')
            ->where('bsp.billing_periode_id', $billingPeriodeId)
            ->where('c.mikrotik_id', $mikrotikId);

        if ($status) {
            $q->where('b.status', $status);
        }

        return [
            'cust' => (clone $q)->distinct('b.customer_id')->count('b.customer_id'),
            'amount' => (clone $q)->sum('b.total_amount'),
        ];
    }
}
