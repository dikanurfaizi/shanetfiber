<?php

namespace App\Services\Customers\Provisioning;

use App\Domain\Decision\DecisionResult;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Billings\BillingSubscriptionPeriode;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Customers\SubscriptionChange;
use App\Models\Websystem;
use App\Services\Customers\Billing\ProratedPackageBillingService;
use DomainException;

class PackageBillingService
{
    public function __construct(
        private ProratedPackageBillingService $proratedService
    ) {}

    public function applySubscriptionChange(
        DecisionResult $decision,
        Customer $customer,
        ?Billing $billing,
        BillingPeriode $period,
        CustomerSubscription $oldSubscription,
        CustomerSubscription $newSubscription,
        string $traceId
    ): void {

        match ($decision->billingStrategy) {

            DecisionResult::STRATEGY_NONE => null,

            DecisionResult::STRATEGY_UPDATE_CURRENT,
            DecisionResult::STRATEGY_SUPPLEMENTAL =>
            $this->proratedService->applyUpgrade(
                customer: $customer,
                billing: $billing,
                billingPeriode: $period,
                billingSubPeriode: $this->resolveBillingSubPeriode(
                    $billing,
                    $period
                ),
                oldSubscription: $oldSubscription,
                newSubscription: $newSubscription,
                changeDate: now(),
                prorateMode: $decision->prorateMode,
                websystem: $this->resolveWebsystem(),
                traceId: $traceId
            ),

            DecisionResult::STRATEGY_DEFER =>
            SubscriptionChange::create([
                'customer_id'         => $customer->id,
                'old_subscription_id' => $oldSubscription->id,
                'new_package_id'      => $newSubscription->package_id,
                'effective_at'        => $period->start_date->copy()->addMonth(),
            ]),
        };
    }

    private function resolveBillingSubPeriode(
        Billing $billing,
        BillingPeriode $period
    ): BillingSubscriptionPeriode {

        $bspId = (int) ($billing->billing_subscription_id ?? 0);

        if ($bspId <= 0) {
            throw new DomainException('Billing does not have billing_subscription_id.');
        }

        $bsp = BillingSubscriptionPeriode::with('billingPeriode')
            ->find($bspId);

        if (!$bsp) {
            throw new DomainException('Billing subscription period not found.');
        }

        // Guard: pastikan BSP ini memang untuk periode yang sedang diproses
        if ((int) $bsp->billing_periode_id !== (int) $period->id) {
            throw new DomainException('Billing subscription period does not match current billing period.');
        }

        return $bsp;
    }


    public function resolveWebsystem(): Websystem
    {
        $websystem = Websystem::where('slug', 'websystem')->first();

        if (!$websystem) {
            throw new DomainException('Websystem configuration not found.');
        }

        return $websystem;
    }
}
