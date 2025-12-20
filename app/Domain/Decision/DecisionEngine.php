<?php

namespace App\Domain\Decision;

class DecisionEngine
{
    public function decidePackageChange(DecisionContext $ctx): DecisionResult
    {
        // NEW SUBSCRIPTION
        if (!$ctx->hasActiveSubscription) {
            return new DecisionResult(
                allowed: true,
                effectivePeriod: DecisionResult::PERIOD_CURRENT,
                billingStrategy: $ctx->currentBilling
                    ? DecisionResult::STRATEGY_UPDATE_CURRENT
                    : DecisionResult::STRATEGY_NONE,
                notes: ['new subscription']
            );
        }

        // SAME PACKAGE
        if ($ctx->isSamePackage()) {
            return new DecisionResult(
                allowed: true,
                effectivePeriod: DecisionResult::PERIOD_CURRENT,
                billingStrategy: DecisionResult::STRATEGY_NONE,
                notes: ['same package']
            );
        }

        // UPGRADE
        if ($ctx->isUpgrade()) {

            if (!$ctx->currentBilling) {
                return new DecisionResult(
                    allowed: true,
                    effectivePeriod: DecisionResult::PERIOD_CURRENT,
                    billingStrategy: DecisionResult::STRATEGY_NONE,
                    notes: ['upgrade, no billing yet']
                );
            }

            if (!$ctx->isSettled()) {
                return new DecisionResult(
                    allowed: true,
                    effectivePeriod: DecisionResult::PERIOD_CURRENT,
                    billingStrategy: DecisionResult::STRATEGY_UPDATE_CURRENT,
                    prorateMode: DecisionResult::PRORATE_ACCUMULATIVE,
                    notes: ['upgrade, unpaid']
                );
            }

            return new DecisionResult(
                allowed: true,
                effectivePeriod: DecisionResult::PERIOD_CURRENT,
                billingStrategy: DecisionResult::STRATEGY_SUPPLEMENTAL,
                prorateMode: DecisionResult::PRORATE_ADJUSTMENT,
                notes: ['upgrade, settled']
            );
        }

        // DOWNGRADE
        if ($ctx->isDowngrade()) {

            if ($ctx->hasOutstandingPast) {
                return new DecisionResult(
                    allowed: false,
                    blockReason: 'Outstanding billing exists'
                );
            }

            if (!$ctx->currentBilling || !$ctx->isSettled()) {
                return new DecisionResult(
                    allowed: true,
                    effectivePeriod: DecisionResult::PERIOD_CURRENT,
                    billingStrategy: DecisionResult::STRATEGY_UPDATE_CURRENT,
                    prorateMode: DecisionResult::PRORATE_ACCUMULATIVE,
                    notes: ['downgrade, unpaid']
                );
            }

            return new DecisionResult(
                allowed: true,
                effectivePeriod: DecisionResult::PERIOD_NEXT,
                billingStrategy: DecisionResult::STRATEGY_DEFER,
                requireSubscriptionChangeRecord: true,
                notes: ['downgrade deferred']
            );
        }

        return new DecisionResult(false, 'Unknown state');
    }

    public function decideAddonChange(DecisionContext $ctx): DecisionResult
    {
        // Tidak ada perubahan addon
        if (!$ctx->hasAddonChange) {
            return new DecisionResult(
                allowed: true,
                billingStrategy: DecisionResult::STRATEGY_NONE
            );
        }

        /* ======================================================
         * REMOVE ADDON (lebih ketat)
         * ====================================================== */
        if ($ctx->hasAddonRemove) {

            // BLOCK jika ada tunggakan lama
            if ($ctx->hasOutstandingPast) {
                return new DecisionResult(
                    allowed: false,
                    blockReason: 'Cannot remove addon with outstanding billing'
                );
            }

            // Billing belum lunas → apply sekarang
            if (!$ctx->isSettled()) {
                return new DecisionResult(
                    allowed: true,
                    effectivePeriod: DecisionResult::PERIOD_CURRENT,
                    billingStrategy: DecisionResult::STRATEGY_UPDATE_CURRENT,
                    notes: ['remove addon, unpaid billing']
                );
            }

            // Billing lunas → defer ke next period
            return new DecisionResult(
                allowed: true,
                effectivePeriod: DecisionResult::PERIOD_NEXT,
                billingStrategy: DecisionResult::STRATEGY_DEFER,
                notes: ['remove addon deferred']
            );
        }

        /* ======================================================
         * ADD / CHANGE ADDON
         * ====================================================== */
        if (!$ctx->currentBilling) {
            return new DecisionResult(
                allowed: true,
                effectivePeriod: DecisionResult::PERIOD_CURRENT,
                billingStrategy: DecisionResult::STRATEGY_NONE,
                notes: ['add addon, no billing yet']
            );
        }

        if (!$ctx->isSettled()) {
            return new DecisionResult(
                allowed: true,
                effectivePeriod: DecisionResult::PERIOD_CURRENT,
                billingStrategy: DecisionResult::STRATEGY_UPDATE_CURRENT,
                notes: ['add addon, unpaid billing']
            );
        }

        return new DecisionResult(
            allowed: true,
            effectivePeriod: DecisionResult::PERIOD_CURRENT,
            billingStrategy: DecisionResult::STRATEGY_SUPPLEMENTAL,
            notes: ['add addon, settled billing']
        );
    }

    public function decideMembershipChange(DecisionContext $ctx): DecisionResult
    {
        if (!$ctx->hasMembershipChange) {
            return new DecisionResult(
                allowed: true,
                billingStrategy: DecisionResult::STRATEGY_NONE
            );
        }

        // Billing belum lunas → boleh current
        if (!$ctx->isSettled()) {
            return new DecisionResult(
                allowed: true,
                effectivePeriod: DecisionResult::PERIOD_CURRENT,
                billingStrategy: DecisionResult::STRATEGY_UPDATE_CURRENT,
                notes: ['membership apply current']
            );
        }

        // Billing lunas → defer
        return new DecisionResult(
            allowed: true,
            effectivePeriod: DecisionResult::PERIOD_NEXT,
            billingStrategy: DecisionResult::STRATEGY_DEFER,
            notes: ['membership deferred']
        );
    }
}
