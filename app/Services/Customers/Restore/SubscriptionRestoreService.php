<?php

namespace App\Services\Customers\Restore;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Packages\PackageDuration;
use InvalidArgumentException;

class SubscriptionRestoreService
{
    public function handle(Customer $customer, array $payload): CustomerSubscription
    {
        $this->validatePayload($payload);

        $subscription = CustomerSubscription::where('customer_id', $customer->id)
            ->lockForUpdate()
            ->latest()
            ->first();

        if (!$subscription) {
            return $this->createSubscription($customer, $payload);
        }

        $subscription = $this->updateSubscription($subscription, $payload);

        return $subscription;
    }

    private function validatePayload(array $payload): void
    {
        foreach (['package_id', 'package_duration_id'] as $key) {
            if (empty($payload[$key])) {
                throw new InvalidArgumentException("Missing required field: {$key}");
            }
        }
    }

    private function createSubscription(
        Customer $customer,
        array $payload
    ): CustomerSubscription {
        /** ----------------------------------------------------
         * 1. Fetch Duration + Package (Eager Loaded)
         * ---------------------------------------------------- */
        $packageDuration = PackageDuration::with(['duration', 'package'])
            ->findOrFail($payload['package_duration_id']);

        $package  = $packageDuration->package;
        $duration = $packageDuration->duration;

        /** ----------------------------------------------------
         * 2. Calculate Pricing
         * ---------------------------------------------------- */
        $months = $duration->duration_months;
        $baseTotal = $package->price * $months;

        $discountType  = 'none';
        $discountValue = 0;
        $priceFinal    = $baseTotal;

        if ($packageDuration->discount_type === 'percent' && $packageDuration->discount_value > 0) {
            $discountType  = 'percent';
            $discountValue = $packageDuration->discount_value;

            $discountAmount = $baseTotal * ($discountValue / 100);
            $priceFinal     = max($baseTotal - $discountAmount, 0);
        } elseif ($packageDuration->discount_type === 'amount' && $packageDuration->discount_value > 0) {

            $discountType  = 'amount';
            $discountValue = $packageDuration->discount_value;

            $priceFinal = max($baseTotal - $discountValue, 0);
        }

        /** ----------------------------------------------------
         * 3. Create Customer Subscription
         * ---------------------------------------------------- */
        $months = $packageDuration->duration->duration_months;
        $endDate = now()->addMonths($months)->day(15);

        $subscription = CustomerSubscription::create([
            'customer_id'         => $customer->id,
            'package_id'          => $package->id,
            'package_duration_id' => $packageDuration->id,
            'status'              => 'active',
            'start_date'          => now(),
            'end_date'            => $endDate,
            'price_final'         => $priceFinal,
            'discount_type_final' => $discountType,
            'discount_value_final' => $discountValue,
        ]);

        return $subscription;
    }


    private function updateSubscription(
        CustomerSubscription $subscription,
        array $payload
    ): CustomerSubscription {
        $subscription->update([
            'package_id'          => $payload['package_id'],
            'package_duration_id' => $payload['package_duration_id'],
            'status'              => 'active',
            'end_date'            => now()->endOfMonth(),
        ]);

        return $subscription;
    }
}
