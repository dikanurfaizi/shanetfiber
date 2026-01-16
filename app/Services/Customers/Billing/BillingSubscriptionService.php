<?php

namespace App\Services\Customers\Billing;

use App\Helpers\LogHelper;
use App\Models\Customers\CustomerSubscription;

class BillingSubscriptionService
{
    public function applyForPeriod(
        CustomerSubscription $oldSubscription,
        CustomerSubscription $newSubscription,
        int $periodId,
        int $oldPackageId,
        int $newPackageId,
        string $traceId
    ): void {
        $q = $oldSubscription->billingSubscriptionPeriodes()
            ->where('billing_periode_id', $periodId)
            ->where('status', 'active');

        $affected = $q->update([
            'customer_subscription_id' => $newSubscription->id,
            'package_id_before' => $oldPackageId,
            'package_id_after' => $newPackageId,
            'package_changed_at' => now()->toDateString(),
        ]);

        LogHelper::logInfo(null, [
            'step' => 'BILLING_SUBSCRIPTION_PERIOD_UPDATED',
            'period_id' => $periodId,
            'affected_rows' => $affected,
            'old_subscription_id' => $oldSubscription->id,
            'new_subscription_id' => $newSubscription->id,
        ], 200, 'Billing Update Flow', $traceId);
    }
}
