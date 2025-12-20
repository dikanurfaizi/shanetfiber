<?php

namespace App\Services\Customers\Subscription;

use App\Domain\Decision\DecisionResult;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;

class SubscriptionChangeService
{
    public function __construct(
        private SubscriptionResolver $subscriptionResolver
    ) {}

    public function applySubscriptionChange(
        DecisionResult $decision,
        Customer $customer,
        ?CustomerSubscription $oldSub,
        array $data,
        BillingPeriode $period,
        string $traceId
    ): CustomerSubscription {

        // NEW
        if (!$oldSub) {
            return $this->subscriptionResolver
                ->createNew($customer, $data, $period, $traceId);
        }

        // SAME PACKAGE (extend)
        if (
            $decision->effectivePeriod === DecisionResult::PERIOD_CURRENT
            && $decision->billingStrategy === DecisionResult::STRATEGY_NONE
        ) {
            $oldSub->update([
                'end_date' => $period->end_date,
            ]);

            return $oldSub;
        }

        // APPLY NOW (upgrade / downgrade unpaid)
        if ($decision->effectivePeriod === DecisionResult::PERIOD_CURRENT) {

            $oldSub->update([
                'status'        => 'terminated',
                'end_date'      => now(),
                'terminated_at' => now(),
            ]);

            return $this->subscriptionResolver
                ->createNew($customer, $data, $period, $traceId);
        }

        // NEXT PERIOD → tidak terminate sekarang
        return $oldSub;
    }
}
