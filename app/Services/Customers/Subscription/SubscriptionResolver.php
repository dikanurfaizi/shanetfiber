<?php

namespace App\Services\Customers\Subscription;

use App\Helpers\LogHelper;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Packages\PackageDuration;
use Carbon\Carbon;

class SubscriptionResolver
{
    /* ==========================================================
     * PRICING — derived from package & duration
     * ========================================================== */
    private function calculatePricing(array $data): array
    {
        $packageDuration = PackageDuration::with(['duration', 'package'])
            ->findOrFail($data['package_duration_id']);

        $package   = $packageDuration->package;
        $months    = $packageDuration->duration->duration_months;
        $baseTotal = $package->price * $months;

        $discountType  = 'none';
        $discountValue = 0;
        $priceFinal    = $baseTotal;

        if (
            $packageDuration->discount_type === 'percent'
            && $packageDuration->discount_value > 0
        ) {
            $discountType  = 'percent';
            $discountValue = $packageDuration->discount_value;
            $priceFinal   -= ($baseTotal * $discountValue / 100);
        }

        if (
            $packageDuration->discount_type === 'amount'
            && $packageDuration->discount_value > 0
        ) {
            $discountType  = 'amount';
            $discountValue = $packageDuration->discount_value;
            $priceFinal   -= $discountValue;
        }

        LogHelper::logInfo(null, [
            'package_id'           => $data['package_id'],
            'package_duration_id'  => $data['package_duration_id'],
            'base_total'          => $baseTotal,
            'final_price'         => $priceFinal,
            'discount_type'       => $discountType,
            'discount_value'      => $discountValue,
        ], 200, "Calculated pricing for subscription", null);

        return [
            max($priceFinal, 0),
            $discountType,
            $discountValue,
        ];
    }

    public function resolveOldSubscription(
        Customer $customer,
        string $traceId
    ): ?CustomerSubscription {

        LogHelper::logInfo(null, [
            'customer_id' => $customer->id,
        ], 200, 'Resolving old subscription (pure read)', $traceId);

        // 1️⃣ Ambil active subscription (kalau ada)
        $active = CustomerSubscription::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        if ($active) {
            LogHelper::logInfo(null, [
                'subscription_id' => $active->id,
                'status'          => 'active',
            ], 200, 'Found active subscription', $traceId);

            return $active;
        }

        // 2️⃣ Fallback: subscription terakhir (historical)
        $latest = CustomerSubscription::where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->first();

        if ($latest) {
            LogHelper::logInfo(null, [
                'subscription_id' => $latest->id,
                'status'          => $latest->status,
            ], 200, 'No active subscription, returning latest', $traceId);
        }

        return $latest;
    }

    public function createNew(
        Customer $customer,
        array $data,
        BillingPeriode $period,
        string $traceId
    ): CustomerSubscription {

        [$priceFinal, $discountType, $discountValue] = $this->calculatePricing($data);

        $subscription = CustomerSubscription::create([
            'customer_id'          => $customer->id,
            'package_id'           => $data['package_id'],
            'package_duration_id'  => $data['package_duration_id'],
            'status'               => 'active',
            'start_date'           => $period->start_date,
            'end_date'             => $period->end_date,
            'price_final'          => $priceFinal,
            'discount_type_final'  => $discountType,
            'discount_value_final' => $discountValue,
        ]);

        LogHelper::logInfo(null, [
            'subscription_id' => $subscription->id,
            'customer_id'     => $customer->id,
            'package_id'      => $data['package_id'],
            'period_start'    => $period->start_date->toDateString(),
            'period_end'      => $period->end_date->toDateString(),
        ], 200, 'Created new customer subscription', $traceId);

        return $subscription;
    }
}
