<?php

namespace App\Services\Customers;

use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Membership\CustomerMembership;
use DomainException;

class CustomerSubscriptionGuardService
{
    public function assertReadyForActivation(Customer $customer): void
    {
        $subscription = CustomerSubscription::where('customer_id', $customer->id)->where('status', 'active')->first();

        throw_if(!$subscription, new DomainException('Subscription belum dipilih'));
        throw_if($subscription->package()->doesntExist(), new DomainException('Package belum dipilih'));
    }
}
