<?php

namespace App\Services\Customers\Billing;

use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use Carbon\Carbon;

class BillingLookupService
{
    /* ==========================================================
     * Helpers: Billing lookup untuk periode aktif
     * ========================================================== */
    public function findBillingForActivePeriod(Customer $customer, BillingPeriode $billingPeriode): ?Billing
    {
        return Billing::whereHas('billingSubscription', function ($q) use ($billingPeriode) {
            $q->where('billing_periode_id', $billingPeriode->id);
        })
            ->where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->first();
    }

    public function isMidBillingPeriod(BillingPeriode $periode, Carbon $changeDate): bool
    {
        return $changeDate->greaterThan($periode->start_date)
            && $changeDate->lessThan($periode->end_date);
    }

    public function resolveCurrentPeriodOrFail(): BillingPeriode
    {
        return BillingPeriode::whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->firstOrFail();
    }

    public function hasOutstandingPastBilling(
        Customer $customer,
        BillingPeriode $currentPeriod
    ): bool {
        return Billing::where('customer_id', $customer->id)
            ->whereHas('billingSubscription.billingPeriode', function ($q) use ($currentPeriod) {
                $q->where('start_date', '<', $currentPeriod->start_date);
            })
            ->where('status', '!=', 'LS')
            ->exists();
    }
}
