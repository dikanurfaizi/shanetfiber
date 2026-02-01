<?php

namespace App\Services\Billings;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Customers\CustomerAddon;
use App\Models\Billings\BillingPeriode;
use Carbon\Carbon;
use DomainException;

class BillingExtensionService
{
    public function extendSubscription(
        CustomerSubscription $sub,
        BillingPeriode $billingPeriode
    ): void {
        $newEndDate = $this->resolveBillingPeriodeEndDate($billingPeriode);

        if ($sub->end_date) {
            $currentEnd = Carbon::parse($sub->end_date)->endOfDay();

            if ($currentEnd->gte($newEndDate)) {
                return;
            }
        }

        $sub->update([
            'end_date' => $newEndDate,
        ]);
    }

    public function extendCustomerAddons(
        Customer $customer,
        BillingPeriode $billingPeriode
    ): void {
        $newEnd = Carbon::parse($billingPeriode->end_date)->endOfDay();

        $addons = CustomerAddon::where('customer_id', $customer->id)
            ->where('is_active', true)
            ->get();

        foreach ($addons as $addon) {
            if (
                !$addon->end_date ||
                Carbon::parse($addon->end_date)->endOfDay()->lt($newEnd)
            ) {
                $addon->update(['end_date' => $newEnd]);
            }
        }
    }

    private function resolveBillingPeriodeEndDate(
        BillingPeriode $billingPeriode
    ): Carbon {
        if ($billingPeriode->end_date) {
            return Carbon::parse($billingPeriode->end_date)->endOfDay();
        }

        try {
            return Carbon::createFromFormat('F Y', $billingPeriode->name)
                ->endOfMonth()
                ->endOfDay();
        } catch (\Throwable $e) {
            throw new DomainException(
                "Invalid billing period format: {$billingPeriode->name}",
                previous: $e
            );
        }
    }
}
