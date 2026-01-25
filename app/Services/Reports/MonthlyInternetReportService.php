<?php

namespace App\Services\Reports;

use App\Models\Billings\BillingPeriode as BillingsBillingPeriode;
use App\Models\User;
use App\Models\Mikrotik; // sesuaikan nama model kamu
use App\Models\Packages\Package as PackagesPackage;
use Illuminate\Support\Facades\DB;

class MonthlyInternetReportService
{
    public function generate(int $userId, int $billingPeriodeId, ?int $mikrotikId = null): array
    {
        $user = User::query()->select('id', 'name')->findOrFail($userId);
        $periode = BillingsBillingPeriode::query()->findOrFail($billingPeriodeId);

        // Ambil mikrotik yang punya customer di bawah user ini
        $mikrotiks = Mikrotik::query()
            ->select('id', 'name')
            ->when($mikrotikId, fn($q) => $q->where('id', $mikrotikId))
            ->whereExists(function ($q) use ($userId) {
                $q->select(DB::raw(1))
                    ->from('customers as c')
                    ->whereColumn('c.mikrotik_id', 'mikrotiks.id')
                    ->where('c.user_id', $userId);
            })
            ->orderBy('name')
            ->get();

        $blocks = [];
        foreach ($mikrotiks as $m) {
            $blocks[] = $this->buildMikrotikBlock($m->id, $m->name, $userId, $billingPeriodeId);
        }

        $grand = $this->calculateGrandTotals($blocks);

        return [
            'periode' => $periode,
            'responsibility' => $user,
            'mikrotiks' => $blocks,
            'grand_totals' => $grand,
        ];
    }

    protected function buildMikrotikBlock(int $mikrotikId, string $mikrotikName, int $userId, int $billingPeriodeId): array
    {
        $packages = PackagesPackage::query()
            ->select('id', 'name', 'price')
            ->orderBy('price')
            ->get();

        $rows = [];
        foreach ($packages as $package) {
            $rows[] = $this->buildPackageRow($package->id, $package->name, (float) $package->price, $userId, $mikrotikId, $billingPeriodeId);
        }

        return [
            'mikrotik_id' => $mikrotikId,
            'mikrotik_name' => $mikrotikName,
            'rows' => $rows,
            'totals' => $this->calculateTotals($rows),
        ];
    }

    protected function buildPackageRow(
        int $packageId,
        string $packageName,
        float $price,
        int $userId,
        int $mikrotikId,
        int $billingPeriodeId
    ): array {

        /**
         * 1️⃣ SUBSCRIPTION CUST (168)
         */
        $subscriptionCust = DB::table('customer_subscriptions as cs')
            ->join('billing_subscription_periodes as bsp', 'bsp.customer_subscription_id', '=', 'cs.id')
            ->join('customers as c', 'c.id', '=', 'cs.customer_id')
            ->where('cs.package_id', $packageId)
            ->where('cs.status', 'active')
            ->where('bsp.billing_periode_id', $billingPeriodeId)
            ->where('c.user_id', $userId)
            ->where('c.mikrotik_id', $mikrotikId)
            ->distinct('cs.customer_id')
            ->count('cs.customer_id');

        /**
         * 2️⃣ UNSUBSCRIPTION (terminated / paused di periode itu)
         */
        $unsubCust = DB::table('customer_subscriptions as cs')
            ->join('billing_subscription_periodes as bsp', 'bsp.customer_subscription_id', '=', 'cs.id')
            ->join('customers as c', 'c.id', '=', 'cs.customer_id')
            ->where('cs.package_id', $packageId)
            ->whereIn('cs.status', ['terminated', 'paused'])
            ->where('bsp.billing_periode_id', $billingPeriodeId)
            ->where('c.user_id', $userId)
            ->where('c.mikrotik_id', $mikrotikId)
            ->distinct('cs.customer_id')
            ->count('cs.customer_id');

        /**
         * 3️⃣ BASE BILLING QUERY (dipakai ulang)
         */
        $billingsBase = DB::table('billings as b')
            ->join('billing_subscription_periodes as bsp', 'b.billing_subscription_id', '=', 'bsp.id')
            ->join('customer_subscriptions as cs', 'bsp.customer_subscription_id', '=', 'cs.id')
            ->join('customers as c', 'c.id', '=', 'cs.customer_id')
            ->where('cs.package_id', $packageId)
            ->where('cs.status', 'active')
            ->where('bsp.billing_periode_id', $billingPeriodeId)
            ->where('c.user_id', $userId)
            ->where('c.mikrotik_id', $mikrotikId);

        /**
         * 4️⃣ TOTAL BILLING
         */
        $billingAmount = (clone $billingsBase)->sum('b.total_amount');
        $billingCust   = (clone $billingsBase)->distinct('b.customer_id')->count('b.customer_id');

        /**
         * 5️⃣ STATUS BREAKDOWN
         */
        $payment   = $this->billingByStatus(clone $billingsBase, 'LS');
        $paylater  = $this->billingByStatus(clone $billingsBase, 'PL');
        $unpayment = $this->billingByStatus(clone $billingsBase, 'BL');

        return [
            'package_id' => $packageId,
            'package_name' => $packageName,
            'price' => $price,

            'total_cust' => $subscriptionCust,

            'billing' => [
                'amount' => (float) $billingAmount,
                'cust' => (int) $billingCust,
            ],
            'payment' => $payment,
            'paylater' => $paylater,
            'unpayment' => $unpayment,

            'subscription' => [
                'cust' => (int) $subscriptionCust,
                'nominal' => $subscriptionCust * $price,
            ],
            'unsubscription' => [
                'cust' => (int) $unsubCust,
                'nominal' => 0,
            ],
        ];
    }

    protected function billingByStatus($billingsBaseQuery, string $status): array
    {
        $q1 = clone $billingsBaseQuery;
        $q2 = clone $billingsBaseQuery;

        return [
            'amount' => (float) $q1->where('b.status', $status)->sum('b.total_amount'),
            'cust' => (int) $q2->where('b.status', $status)->distinct('b.customer_id')->count('b.customer_id'),
        ];
    }

    protected function calculateTotals(array $rows): array
    {
        $totals = [
            'total_cust' => 0,

            'billing_amount' => 0,
            'billing_cust' => 0,

            'payment_amount' => 0,
            'payment_cust' => 0,

            'unpayment_amount' => 0,
            'unpayment_cust' => 0,

            'paylater_amount' => 0,
            'paylater_cust' => 0,

            'subscription_cust' => 0,
            'subscription_nominal' => 0,

            'unsubscription_cust' => 0,
            'unsubscription_nominal' => 0,
        ];

        foreach ($rows as $r) {
            $totals['total_cust'] += (int) ($r['total_cust'] ?? 0);

            $totals['billing_amount'] += (float) ($r['billing']['amount'] ?? 0);
            $totals['billing_cust'] += (int) ($r['billing']['cust'] ?? 0);

            $totals['payment_amount'] += (float) ($r['payment']['amount'] ?? 0);
            $totals['payment_cust'] += (int) ($r['payment']['cust'] ?? 0);

            $totals['unpayment_amount'] += (float) ($r['unpayment']['amount'] ?? 0);
            $totals['unpayment_cust'] += (int) ($r['unpayment']['cust'] ?? 0);

            $totals['paylater_amount'] += (float) ($r['paylater']['amount'] ?? 0);
            $totals['paylater_cust'] += (int) ($r['paylater']['cust'] ?? 0);

            $totals['subscription_cust'] += (int) ($r['subscription']['cust'] ?? 0);
            $totals['subscription_nominal'] += (float) ($r['subscription']['nominal'] ?? 0);

            $totals['unsubscription_cust'] += (int) ($r['unsubscription']['cust'] ?? 0);
            $totals['unsubscription_nominal'] += (float) ($r['unsubscription']['nominal'] ?? 0);
        }

        return $totals;
    }

    protected function calculateGrandTotals(array $blocks): array
    {
        $grandRows = [];

        // gabungkan semua rows seluruh mikrotik lalu total-kan
        foreach ($blocks as $b) {
            foreach ($b['rows'] as $r) {
                $grandRows[] = $r;
            }
        }

        return $this->calculateTotals($grandRows);
    }
}
