<?php

namespace App\Services\Customers\Billing;

use App\Domain\Decision\DecisionContext;
use App\Domain\Decision\DecisionEngine;
use App\Domain\Decision\DecisionResult;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Services\Customers\Addons\AddonDiffService;
use App\Services\Customers\Billing\BillingLookupService;
use App\Services\Customers\Provisioning\PackageBillingService;
use App\Services\Customers\Subscription\SubscriptionChangeService;
use App\Services\Customers\Subscription\SubscriptionResolver;
use App\Services\Packages\PackageDataServive;
use App\Services\Customers\Addons\AddonChangeService;
use App\Services\Customers\Membership\MembershipChangeService;
use App\Services\Customers\Provisioning\ChangePackageService;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UpdateService
{
    public function __construct(
        private AddonDiffService $addonDiffService,
        private SubscriptionResolver $subscriptionResolver,
        private PackageDataServive $packageDataService,
        private DecisionEngine $decisionEngine,
        private BillingLookupService $billingLookupService,
        private SubscriptionChangeService $subscriptionChangeService,
        private PackageBillingService $packageBillingService,
        private AddonChangeService $addonChangeService,
        private MembershipChangeService $membershipChangeService,
        private ChangePackageService $changePackageService
    ) {}

    /**
     * Update subscription & membership, then regenerate billing
     * ONLY for latest existing billing period
     */
    public function updateAndRegenerate(
        Customer $customer,
        array $data,
        string $traceId,
        float $startTime
    ): Customer {

        return DB::transaction(function () use ($customer, $data, $traceId) {

            $this->stepLog('START_UPDATE', [
                'customer_id' => $customer->id,
                'payload_keys' => array_keys($data),
            ], $traceId);

            // ==========================================================
            // 1) Resolve PERIOD & BILLING CONTEXT
            // ==========================================================
            $currentPeriod = $this->billingLookupService
                ->resolveCurrentPeriodOrFail();

            $currentBilling = $this->billingLookupService
                ->findBillingForActivePeriod($customer, $currentPeriod);

            $hasOutstandingPast = $this->billingLookupService
                ->hasOutstandingPastBilling($customer, $currentPeriod);

            // ==========================================================
            // 2) Resolve CURRENT STATE (subscription / addon / membership)
            // ==========================================================
            $oldSubscription = $this->subscriptionResolver
                ->resolveOldSubscription($customer, $traceId);

            $packageData = $this->packageDataService
                ->getById([(int) ($data['package_id'] ?? 0)]);

            $newPrice = (float) ($packageData[$data['package_id']]['price'] ?? 0);
            $oldPrice = $oldSubscription?->package->price ?? 0;

            $newPackageId = $data['package_id'];
            $oldPackageId = $oldSubscription?->package_id;

            $incomingAddonMap = null;
            $addonDiff = null;

            if (array_key_exists('addons', $data)) {
                $incomingAddonMap = $this->addonDiffService->normalizeAddons(
                    $data['addons'] instanceof Collection
                        ? $data['addons']->all()
                        : $data['addons']
                );

                $addonDiff = $this->addonDiffService->diff($customer, $incomingAddonMap);
            }

            $this->stepLog('CONTEXT_RESOLVED', [
                'current_period_id' => $currentPeriod->id,
                'current_billing_id' => $currentBilling?->id,
                'has_outstanding_past' => $hasOutstandingPast,
                'old_subscription_id' => $oldSubscription?->id,
                'has_addon_diff' => $addonDiff['has_change'] ?? false,
            ], $traceId);

            // ==========================================================
            // 3) BUILD DECISION CONTEXT
            // ==========================================================
            $context = new DecisionContext(
                currentPeriod: $currentPeriod,
                currentBilling: $currentBilling,
                hasOutstandingPast: $hasOutstandingPast,
                hasActiveSubscription: (bool) $oldSubscription,
                oldPrice: $oldPrice,
                newPrice: $newPrice,
                hasAddonChange: $addonDiff['has_change'] ?? false,
                hasMembershipChange: array_key_exists('member_id', $data),
                oldPackageId: $oldPackageId,
                newPackageId: $newPackageId,
            );

            if ($addonDiff) {
                $context->hasAddonAddOrChange =
                    !empty($addonDiff['added']) || !empty($addonDiff['changed']);

                $context->hasAddonRemove =
                    !empty($addonDiff['removed']);
            }

            // ==========================================================
            // 4) DECIDE (PURE LOGIC)
            // ==========================================================
            $packageDecision    = $this->decisionEngine->decidePackageChange($context);
            $addonDecision      = $this->decisionEngine->decideAddonChange($context);
            $membershipDecision = $this->decisionEngine->decideMembershipChange($context);

            $this->stepLog('DECISION_READY', [
                'package' => [
                    'strategy' => $packageDecision->billingStrategy,
                    'period'   => $packageDecision->effectivePeriod,
                ],
                'addon' => [
                    'strategy' => $addonDecision->billingStrategy,
                    'period'   => $addonDecision->effectivePeriod,
                ],
                'membership' => [
                    'strategy' => $membershipDecision->billingStrategy,
                    'period'   => $membershipDecision->effectivePeriod,
                ],
            ], $traceId);


            // ==========================================================
            // 5) GUARD
            // ==========================================================
            foreach ([$packageDecision, $addonDecision, $membershipDecision] as $decision) {
                if (!$decision->allowed) {
                    throw new DomainException($decision->blockReason);
                }
            }

            // ==========================================================
            // 6) EXECUTE (SIDE EFFECTS)
            // ==========================================================
            $newSubscription = $oldSubscription;

            if ($packageDecision->billingStrategy !== DecisionResult::STRATEGY_NONE) {
                $this->stepLog('BEFORE_SUBSCRIPTION_CHANGE', [
                    'strategy' => $packageDecision->billingStrategy,
                ], $traceId);
                $newSubscription = $this->subscriptionChangeService->applySubscriptionChange(
                    $packageDecision,
                    $customer,
                    $oldSubscription,
                    $data,
                    $currentPeriod,
                    $traceId
                );
            }

            $this->stepLog('AFTER_SUBSCRIPTION_CHANGE', [
                'new_subscription_id' => $newSubscription?->id,
            ], $traceId);


            $this->stepLog('BEFORE_PACKAGE_BILLING', [
                'strategy' => $packageDecision->billingStrategy,
            ], $traceId);

            if ($packageDecision->billingStrategy !== DecisionResult::STRATEGY_NONE) {
                $this->packageBillingService->applySubscriptionChange(
                    $packageDecision,
                    $customer,
                    $currentBilling,
                    $currentPeriod,
                    $oldSubscription,
                    $newSubscription,
                    $traceId
                );
            }

            $this->stepLog('AFTER_PACKAGE_BILLING', [], $traceId);

            if (
                $packageDecision->allowed &&
                $packageDecision->effectivePeriod === DecisionResult::PERIOD_CURRENT &&
                $packageDecision->shouldApplyPackageEntitlement &&
                in_array(
                    $packageDecision->billingStrategy,
                    [
                        DecisionResult::STRATEGY_UPDATE_CURRENT,
                        DecisionResult::STRATEGY_SUPPLEMENTAL,
                    ],
                    true
                )
            ) {
                $this->changePackageService->applyPackageChange(
                    $customer,
                    $oldSubscription,
                    $newSubscription,
                    $traceId
                );
            }

            if ($addonDecision->billingStrategy === DecisionResult::STRATEGY_DEFER) {
                // $this->addonChangeService->scheduleForNextPeriod(
                //     $customer,
                //     $incomingAddonMap,
                //     $addonDiff,
                //     $traceId
                // );
                $this->stepLog('ADDON_DEFERRED', [
                    'effective_period' => $addonDecision->effectivePeriod,
                ], $traceId);
            } else {
                $this->stepLog('BEFORE_ADDON_APPLY', [
                    'strategy' => $addonDecision->billingStrategy,
                    'effective_period' => $addonDecision->effectivePeriod,
                    'current_billing_id' => $currentBilling?->id,
                ], $traceId);

                // schedule / defer: jangan sentuh billing sekarang
                $this->addonChangeService->applyAddonChange(
                    $addonDecision,
                    $customer,
                    $currentBilling,
                    $currentPeriod,
                    $incomingAddonMap,
                    $addonDiff,
                    $traceId
                );

                $this->stepLog('AFTER_ADDON_APPLY', [], $traceId);
            }

            if ($membershipDecision->effectivePeriod === DecisionResult::PERIOD_CURRENT) {
                $this->stepLog('BEFORE_MEMBERSHIP_APPLY', [
                    'strategy' => $membershipDecision->billingStrategy,
                ], $traceId);

                $this->membershipChangeService->applyMembershipChange(
                    $membershipDecision,
                    $customer,
                    $data,
                    $currentPeriod,
                    $currentBilling,
                    $traceId
                );

                $this->stepLog('AFTER_MEMBERSHIP_APPLY', [], $traceId);
            } else {
                // $this->membershipChangeService->scheduleForNextPeriod(...);

                $this->stepLog('MEMBERSHIP_DEFERRED', [
                    'effective_period' => $membershipDecision->effectivePeriod,
                ], $traceId);
            }

            $this->stepLog('END_UPDATE', [
                'status' => 'success'
            ], $traceId);

            return $customer;
        });
    }

    private function stepLog(string $step, array $data, string $traceId): void
    {
        LogHelper::logInfo(
            null,
            array_merge(['step' => $step], $data),
            200,
            'Billing Update Flow',
            $traceId
        );
    }
}
