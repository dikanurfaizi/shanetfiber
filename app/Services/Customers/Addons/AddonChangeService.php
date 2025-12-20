<?php

namespace App\Services\Customers\Addons;

use App\Domain\Decision\DecisionResult;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;

class AddonChangeService
{
    public function __construct(
        private AddonStateService $addonStateService,
        private AddonBillingSyncService $addonBillingSyncService
    ) {}

    public function applyAddonChange(
        $decision,
        Customer $customer,
        ?Billing $billing,
        BillingPeriode $period,
        ?array $incomingAddonMap,
        ?array $addonDiff,
        string $traceId
    ): void {

        if (!$addonDiff || empty($addonDiff['has_change'])) {
            return;
        }

        // DEFER REMOVE → apply ke periode berikutnya
        if ($decision->billingStrategy === DecisionResult::STRATEGY_DEFER) {

            $nextPeriod = $period->next();

            $this->addonStateService->apply(
                $customer,
                $incomingAddonMap,
                $addonDiff,
                $nextPeriod,
                $traceId
            );

            return;
        }

        // sisanya SAMA dengan Step 6B
        if ($decision->billingStrategy === DecisionResult::STRATEGY_NONE) {
            $this->addonStateService->apply(
                $customer,
                $incomingAddonMap,
                $addonDiff,
                $period,
                $traceId
            );
            return;
        }

        if ($decision->billingStrategy === DecisionResult::STRATEGY_UPDATE_CURRENT) {

            $this->addonStateService->apply(
                $customer,
                $incomingAddonMap,
                $addonDiff,
                $period,
                $traceId
            );

            $this->addonBillingSyncService->sync(
                $billing,
                $incomingAddonMap,
                $addonDiff
            );
            return;
        }

        if ($decision->billingStrategy === DecisionResult::STRATEGY_SUPPLEMENTAL) {

            $this->addonStateService->apply(
                $customer,
                $incomingAddonMap,
                $addonDiff,
                $period,
                $traceId
            );

            $this->addonBillingSyncService->sync(
                $billing,
                $incomingAddonMap,
                $addonDiff
            );
        }
    }
}
