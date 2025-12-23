<?php

namespace App\Services\Customers\Restore;

use App\Helpers\PeriodHelper;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Packages\Package;
use App\Models\Membership\Membership;
use App\Models\Packages\Addon as PackagesAddon;
use App\Services\Billing\BillingDiscountEstimator;
use App\Services\Packages\PackageGetService;

class RestoreContextResolver
{
    public function __construct(private PackageGetService $packageGetService) {}

    public function resolve(
        Customer $customer,
        ?CustomerSubscription $subscription,
        string $mode
    ): RestoreContext {
        $context = new RestoreContext();
        $context->mode = $mode;

        // ==========================
        // Load selectable resources
        // ==========================
        $context->packages = Package::active()->get();
        $context->members  = Membership::active()->get();
        $context->addons   = PackagesAddon::active()->get();

        $context->availablePeriodes = PeriodHelper::generate(3);

        // ==========================
        // Resolve base config
        // ==========================
        if ($subscription) {
            $this->resolveFromExistingSubscription(
                $context,
                $customer,
                $subscription
            );
        } else {
            $this->resolveForNewSubscription($context);
        }

        return $context;
    }

    /**
     * Resolve context when customer HAS previous subscription.
     */
    private function resolveFromExistingSubscription(
        RestoreContext $context,
        Customer $customer,
        CustomerSubscription $subscription
    ): void {
        // ==========================
        // Package
        // ==========================
        if ($subscription->package && $subscription->package->isActive()) {
            $context->current_package_id = $subscription->package_id;
        } else {
            $context->requirePackageSelection = true;
        }

        // ==========================
        // Duration
        // ==========================
        if (
            $subscription->packageDuration &&
            $subscription->packageDuration->is_active
        ) {
            $context->current_duration_id = $subscription->package_duration_id;
            $context->current_duration_name =
                $subscription->packageDuration->duration->name_en
                ?? $subscription->packageDuration->name
                ?? 'Unknown Duration';
        } else {
            $context->requireDurationSelection = true;
        }

        // ==========================
        // Membership
        // ==========================
        $membershipId = null;

        if ($customer->activeMembership) {
            // pilih SATU field yang benar-benar ada
            $membershipId = $customer->activeMembership?->membership?->id
                ?? null;
        }

        $context->current_membership_id = $membershipId;

        // ==========================
        // Billing option
        // ==========================
        $context->billing_option =
            $customer->activeMembership?->end_date !== null ? 'monthly' : 'one_time';

        // ==========================
        // Addons
        // ==========================
        $context->activeCustomerAddons =
            $customer->activeAddonsIndexed();

        // ==========================
        // Discount preview (non-final)
        // ==========================
        $context->discountEstimate = $this->packageGetService->getDiscount(
            $subscription->package_id,
            $membershipId ?? 0
        );
    }

    /**
     * Resolve context when customer HAS NO subscription.
     */
    private function resolveForNewSubscription(
        RestoreContext $context
    ): void {
        $context->requirePackageSelection  = true;
        $context->requireDurationSelection = true;

        $context->activeCustomerAddons = collect();
        $context->discountEstimate = null;
    }
}
