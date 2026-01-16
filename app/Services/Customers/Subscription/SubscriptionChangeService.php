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


        $oldSub->update([
            'status'        => 'terminated',
            'end_date'      => now(),
            'terminated_at' => now(),
        ]);

        return $this->subscriptionResolver
            ->createNew($customer, $data, $period, $traceId);
    }
}
