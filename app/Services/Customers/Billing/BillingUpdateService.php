<?php

namespace App\Services\Customers\Billing;

use App\Events\PackageChangesApplied;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Websystem;
use App\Services\Billings\BillingCreateService;
use App\Services\Customers\Billing\BillingLookupService;
use App\Services\Customers\Subscription\SubscriptionResolver;
use App\Services\Customers\Addons\AddonChangeService;
use App\Services\Customers\Addons\AddonDiffService;
use App\Services\Customers\Membership\MembershipChangeService;
use App\Services\Customers\Mikrotik\ActionToMikrotikService;
use App\Services\Customers\Subscription\SubscriptionChangeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BillingUpdateService
{
    public function __construct(
        private BillingRuleGuardService $billingRuleGuardService,
        private SubscriptionChangeService $subscriptionChangeService,
        private BillingRecalculatorService $billingRecalculatorService,
        private BillingCreateService $billingCreateService,
        private SubscriptionResolver $subscriptionResolver,
        private BillingLookupService $billingLookupService,
        private AddonChangeService $addonChangeService,
        private AddonDiffService $addonDiffService,
        private MembershipChangeService $membershipChangeService,
        private ActionToMikrotikService $actionToMikrotikService,
        private BillingSubscriptionService $billingSubscriptionService
    ) {}

    public function update(Customer $customer, array $data, string $traceId): array
    {
        $websystem = Websystem::where('slug', 'websystem')->first();

        $packageChanged = false;
        $oldPackageName = null;
        $oldPackagePrice = null;

        $results = DB::transaction(function () use (
            $customer,
            $data,
            $websystem,
            $traceId,
            &$packageChanged,
            &$oldPackageName,
            &$oldPackagePrice
        ) {

            $results = [
                'membership' => ['status' => 'SKIPPED'],
                'addon'      => ['status' => 'SKIPPED'],
                'package'    => ['status' => 'SKIPPED'],
            ];

            LogHelper::logInfo(null, [
                'step' => 'START_UPDATE',
                'customer_id' => $customer->id,
                'payload_keys' => array_keys($data),
            ], 200, 'Billing Update Flow', $traceId);

            // =============================
            // CONTEXT
            // =============================
            $currentPeriod   = $this->billingLookupService->resolveCurrentPeriodOrFail();
            $currentBilling  = $this->billingLookupService->findBillingForActivePeriod($customer, $currentPeriod);
            $oldSubscription = $this->subscriptionResolver->resolveOldSubscription($customer, $traceId);

            $oldPackageName  = $oldSubscription?->package?->name;
            $oldPackagePrice = $oldSubscription?->package?->price;

            // =============================
            // MEMBERSHIP
            // =============================
            if (array_key_exists('member_id', $data)) {
                $results['membership'] = $this->safeStep('MEMBERSHIP', $traceId, function () use (
                    $customer,
                    $data,
                    $currentPeriod,
                    $currentBilling,
                    $traceId
                ) {

                    $billingOption = $data['billing_option'] ?? 'monthly';
                    $periodeName  = $data['billing_periode_name'] ?? null;

                    $periodeScope = $periodeName
                        ? (
                            $this->billingRuleGuardService->isSamePeriod($periodeName, $currentPeriod)
                            ? 'current'
                            : 'next'
                        )
                        : 'next';

                    $memberId = (int) ($data['member_id'] ?? 0);

                    [$ok, $mode, $updateBilling, $reason] =
                        $this->billingRuleGuardService->canChangeMembership(
                            $currentBilling,
                            $memberId,
                            $billingOption,
                            $periodeScope
                        );

                    if (!$ok) {
                        LogHelper::logInfo(null, [
                            'step' => 'MEMBERSHIP_BLOCKED',
                            'reason' => $reason,
                            'member_id' => $memberId,
                            'periode_scope' => $periodeScope,
                        ], 200, 'Billing Update Flow', $traceId);

                        return ['status' => 'BLOCKED', 'reason' => $reason];
                    }

                    $this->membershipChangeService->apply(
                        customer: $customer,
                        data: $data,
                        period: $currentPeriod,
                        billing: $currentBilling,
                        traceId: $traceId,
                        mode: $mode,
                        updateBilling: $updateBilling
                    );

                    return ['status' => 'SUCCESS'];
                });
            }

            // =============================
            // ADDON
            // =============================
            if (isset($data['addons'])) {
                $results['addon'] = $this->safeStep('ADDON', $traceId, function () use (
                    $customer,
                    $data,
                    $currentBilling,
                    $currentPeriod,
                    $traceId
                ) {

                    $normalized = $this->addonDiffService->normalizeAddons(
                        $data['addons'] instanceof Collection
                            ? $data['addons']->toArray()
                            : $data['addons']
                    );

                    $diff = $this->addonDiffService->diff($customer, $normalized);

                    if (!$diff['has_change']) {
                        LogHelper::logInfo(null, [
                            'step' => 'ADDON_NO_CHANGE',
                        ], 200, 'Billing Update Flow', $traceId);

                        return ['status' => 'SKIPPED'];
                    }

                    $this->addonChangeService->applyAddonChange(
                        $customer,
                        $currentBilling,
                        $currentPeriod,
                        $normalized,
                        $diff,
                        $traceId
                    );

                    return ['status' => 'SUCCESS'];
                });
            }

            // =============================
            // PACKAGE
            // =============================
            if (isset($data['package_id'])) {
                $results['package'] = $this->safeStep('PACKAGE', $traceId, function () use (
                    $customer,
                    $data,
                    $currentBilling,
                    $currentPeriod,
                    $oldSubscription,
                    $websystem,
                    $traceId,
                    &$packageChanged
                ) {

                    if ($this->billingRuleGuardService
                        ->isSamePackage($oldSubscription, (int)$data['package_id'])
                    ) {
                        LogHelper::logInfo(null, [
                            'step' => 'PACKAGE_NO_CHANGE',
                        ], 200, 'Billing Update Flow', $traceId);

                        return ['status' => 'SKIPPED'];
                    }

                    [$ok, $reason] = $this->billingRuleGuardService->canChangePackage($currentBilling);

                    if (!$ok) {
                        LogHelper::logInfo(null, [
                            'step' => 'PACKAGE_BLOCKED',
                            'reason' => $reason,
                        ], 200, 'Billing Update Flow', $traceId);

                        return ['status' => 'BLOCKED', 'reason' => $reason];
                    }

                    $newSubscription = $this->subscriptionChangeService->applySubscriptionChange(
                        $customer,
                        $oldSubscription,
                        $data,
                        $currentPeriod,
                        $traceId
                    );

                    $this->billingRecalculatorService->recalcPackage(
                        $currentBilling,
                        $newSubscription,
                        $websystem,
                        $this->billingCreateService
                    );

                    $this->billingSubscriptionService->applyForPeriod(
                        oldSubscription: $oldSubscription,
                        newSubscription: $newSubscription,
                        periodId: $currentPeriod->id,
                        oldPackageId: (int)$oldSubscription->package_id,
                        newPackageId: (int)$newSubscription->package_id,
                        traceId: $traceId
                    );

                    $this->actionToMikrotikService->apply(
                        $customer,
                        $oldSubscription,
                        $newSubscription,
                        $traceId
                    );

                    $packageChanged = true;

                    return ['status' => 'SUCCESS'];
                });
            }

            LogHelper::logInfo(null, [
                'step' => 'END_UPDATE',
                'results' => $results,
                'old_package_name' => $oldPackageName,
                'old_package_price' => $oldPackagePrice
            ], 200, 'Billing Update Flow', $traceId);

            $events = [];

            if ($packageChanged && $oldPackageName && $oldPackagePrice) {
                $events[] = new PackageChangesApplied(
                    $customer,
                    $oldPackageName,
                    $oldPackagePrice,
                    null,
                    $traceId
                );

                LogHelper::logInfo(null, [
                    'step' => 'EVENT_PREPARED',
                    'package_changed' => $packageChanged,
                    'old_package_name' => $oldPackageName,
                    'old_package_price' => $oldPackagePrice,
                    'event_count' => count($events)
                ], 200, 'Billing Update Flow', $traceId);
            } else {
                LogHelper::logInfo(null, [
                    'step' => 'EVENT_SKIPPED',
                    'package_changed' => $packageChanged,
                    'old_package_name' => $oldPackageName,
                    'old_package_price' => $oldPackagePrice,
                    'event_count' => count($events)
                ], 200, 'Billing Update Flow', $traceId);
            }

            return [
                'results' => $results,
                'events' => $events
            ];
        });

        return $results;
    }

    private function safeStep(string $step, string $traceId, callable $fn): array
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'step' => $step,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Billing Update Flow', $traceId);

            return [
                'status' => 'FAILED',
                'reason' => $e->getMessage()
            ];
        }
    }
}
