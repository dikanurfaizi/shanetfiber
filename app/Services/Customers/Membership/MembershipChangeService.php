<?php

namespace App\Services\Customers\Membership;

use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use Carbon\Carbon;

class MembershipChangeService
{
    public function __construct(
        private MembershipResolver $membershipResolver
    ) {}

    public function apply(
        Customer $customer,
        array $data,
        BillingPeriode $period,
        Billing $billing,
        string $traceId,
        string $mode,
        bool $updateBilling
    ): void {

        LogHelper::logInfo(null, [
            'step' => 'MEMBERSHIP_APPLY_ENTER',
            'member_id' => $data['member_id'] ?? null,
            'mode' => $mode,
            'update_billing' => $updateBilling
        ], 200, 'Membership Change Flow', $traceId);

        // ===============================
        // REMOVE MEMBERSHIP
        // ===============================
        if ($mode === 'remove') {

            LogHelper::logInfo(null, [
                'step' => 'MEMBERSHIP_REMOVE'
            ], 200, 'Membership Change Flow', $traceId);

            $this->membershipResolver->remove($customer, $traceId);

            return;
        }

        // ===============================
        // APPLY NEXT PERIOD
        // ===============================
        if ($mode === 'next') {

            $periodeName = $data['billing_periode_name'] ?? null;

            if (!$periodeName) {
                throw new \InvalidArgumentException('billing_periode_name required for next membership');
            }

            $targetStart = Carbon::createFromFormat('F Y', $periodeName)->startOfMonth();
            $targetEnd   = $targetStart->copy()->endOfMonth();

            LogHelper::logInfo(null, [
                'step' => 'MEMBERSHIP_SCHEDULE_NEXT',
                'start' => $targetStart->toDateString(),
                'end' => $targetEnd->toDateString()
            ], 200, 'Membership Change Flow', $traceId);

            $this->membershipResolver->scheduleNext(
                $customer,
                $data,
                $targetStart,
                $targetEnd,
                $traceId
            );

            return;
        }

        // ===============================
        // APPLY CURRENT
        // ===============================
        LogHelper::logInfo(null, [
            'step' => 'MEMBERSHIP_APPLY_CURRENT'
        ], 200, 'Membership Change Flow', $traceId);

        $this->membershipResolver->applyNow(
            $customer,
            $data,
            $period,
            $billing,
            $traceId,
            $updateBilling
        );
    }
}
