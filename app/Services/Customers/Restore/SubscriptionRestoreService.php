<?php

namespace App\Services\Customers\Restore;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
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

        $this->updateSubscription($subscription, $payload);

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
        return CustomerSubscription::create([
            'customer_id'          => $customer->id,
            'package_id'           => $payload['package_id'],
            'package_duration_id'  => $payload['package_duration_id'],
            'status'               => 'active',
            'start_date'           => now(),
            'end_date'             => now()->endOfMonth()
        ]);
    }


    private function updateSubscription(
        CustomerSubscription $subscription,
        array $payload
    ): void {
        $subscription->update([
            'package_id'          => $payload['package_id'],
            'package_duration_id' => $payload['package_duration_id'],
            'status'              => 'active',
            'end_date'            => now()->endOfMonth(),
        ]);
    }
}
