<?php

namespace App\Services\Customers\Billing;

use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\CustomerSubscription;
use App\Models\Membership\CustomerMembership;
use App\Models\Packages\Package;
use Carbon\Carbon;
use DomainException;

class BillingRuleGuardService
{
    public function canChangePackage(?Billing $billing): array
    {
        if (!$billing) {
            return [false, 'Billing not found for current period'];
        }

        if (!in_array($billing->status, ['LS', 'VD'], true)) {
            return [false, 'Package change only allowed when billing is settled/marked'];
        }

        // Cek sudah pernah ubah package di period ini
        // (lebih aman cek dari billing_subscription_periodes untuk period aktif)
        if ($billing->billingSubscription && $billing->billingSubscription->package_changed_at) {
            return [false, 'Package change only allowed 1 time in a month'];
        }

        return [true, null];
    }

    public function isSamePackage(?CustomerSubscription $sub, int $packageId): bool
    {
        return $sub && (int)$sub->package_id === (int)$packageId;
    }

    public function canChangeMembership(
        ?Billing $billing,
        int $memberId,
        string $billingOption,
        string $periodeScope
    ): array {

        // REMOVE MEMBERSHIP → ALWAYS OK
        if ($memberId === 0) {
            return [true, 'remove', false, null];
        }

        if (!$billing) {
            return [false, null, false, 'Billing not found'];
        }

        if (in_array($billing->status, ['LS', 'VD'], true)) {

            if ($billingOption === 'monthly') {
                return [true, 'current', false, null];
            }

            if ($billingOption === 'one_time') {
                if ($periodeScope === 'current') {
                    return [
                        false,
                        null,
                        false,
                        'One time membership cannot be applied to current settled billing'
                    ];
                }

                return [true, 'next', false, null];
            }
        }

        if (!in_array($billing->status, ['LS', 'VD'], true)) {

            if ($periodeScope === 'current') {
                return [true, 'current', true, null];
            }

            return [true, 'next', false, null];
        }

        return [false, null, false, 'Membership rule not matched'];
    }

    public function isSamePeriod(string $billingPeriodeName, BillingPeriode $currentPeriod): bool
    {
        $frontend = Carbon::createFromFormat('F Y', $billingPeriodeName);
        return $frontend->format('Y-m') === $currentPeriod->start_date->format('Y-m');
    }
}
