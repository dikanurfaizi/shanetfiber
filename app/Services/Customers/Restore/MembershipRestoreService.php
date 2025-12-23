<?php

namespace App\Services\Customers\Restore;

use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use App\Models\Membership\CustomerMembership;
use InvalidArgumentException;

class MembershipRestoreService
{
    public function handle(Customer $customer, array $payload): void
    {
        $billingOption = $payload['billing_option'] ?? 'monthly';

        if (
            $customer->activeMembership &&
            $customer->activeMembership->membership->id == $payload['member_id'] &&
            $billingOption === 'monthly'
        ) {
            return;
        }

        // Nonaktifkan membership lama
        $customer->customerMembership()
            ->update(['is_active' => 0]);

        if (empty($payload['member_id'])) {
            return; // non-member
        }

        if ($billingOption === 'monthly') {
            $this->applyMonthlyMembership($customer, $payload);
        } else {
            $this->applyOneTimeMembership($customer, $payload);
        }
    }

    private function applyMonthlyMembership(Customer $customer, array $payload): void
    {
        CustomerMembership::create([
            'customer_id'  => $customer->id,
            'membership_id'    => $payload['member_id'],
            'start_date'   => now(),
            'end_date'     => null, // <-- EXPLICIT
            'is_active'    => 1,
        ]);
    }

    private function applyOneTimeMembership(Customer $customer, array $payload): void
    {
        if (empty($payload['billing_periode_name'])) {
            throw new InvalidArgumentException('billing_periode_name is required for one-time membership');
        }

        $startOfPeriod = $this->parseBillingPeriod($payload['billing_periode_name']);

        CustomerMembership::create([
            'customer_id' => $customer->id,
            'membership_id'   => $payload['member_id'],
            'start_date'  => now(),
            'end_date'    => $startOfPeriod->endOfMonth(), // ← TIDAK tergantung DB
            'is_active'   => 1,
        ]);
    }

    private function parseBillingPeriod(string $period): \Carbon\Carbon
    {
        // Expected format: YYYY-MM (contoh: 2026-01)
        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            throw new InvalidArgumentException("Invalid billing period format: {$period}");
        }

        return \Carbon\Carbon::createFromFormat('Y-m', $period)->startOfMonth();
    }
}
