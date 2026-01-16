<?php

namespace App\Services\Customers\Addons;

use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;

class AddonChangeService
{
    public function __construct(
        private AddonStateService $addonStateService,
        private AddonBillingSyncService $addonBillingSyncService,
    ) {}

    public function applyAddonChange(
        Customer $customer,
        ?Billing $billing,
        BillingPeriode $period,
        ?array $incomingAddonMap,
        ?array $addonDiff,
        string $traceId
    ): void {

        LogHelper::logInfo(null, [
            'step' => 'ADDON_APPLY_ENTER',
            'incoming_type' => gettype($incomingAddonMap),
            'incoming_count' => is_array($incomingAddonMap) ? count($incomingAddonMap) : null,
            'has_diff' => $addonDiff['has_change'] ?? null,
        ], 200, 'Addon Change Flow', $traceId);

        if (!$addonDiff || empty($addonDiff['has_change'])) {
            LogHelper::logInfo(null, [
                'step' => 'ADDON_APPLY_SKIPPED',
                'reason' => 'no_change'
            ], 200, 'Addon Change Flow', $traceId);
            return;
        }

        $this->addonStateService->apply(
            $customer,
            $incomingAddonMap,
            $addonDiff,
            $period,
            $traceId
        );

        LogHelper::logInfo(null, [
            'step' => 'ADDON_STATE_APPLIED'
        ], 200, 'Addon Change Flow', $traceId);

        $this->addonBillingSyncService->sync(
            $billing,
            $incomingAddonMap,
            $addonDiff
        );

        LogHelper::logInfo(null, [
            'step' => 'ADDON_BILLING_SYNCED'
        ], 200, 'Addon Change Flow', $traceId);
    }
}
