<?php

namespace App\Services\Customers\Membership;

use App\Domain\Decision\DecisionResult;
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

    public function applyMembershipChange(
        $decision,
        Customer $customer,
        array $data,
        BillingPeriode $period,
        Billing $billing,
        string $traceId
    ): void {

        LogHelper::logInfo(
            null,
            [
                'member_id' => $data['member_id'] ?? null
            ],
            200,
            "MembershipChangeService::applyMembershipChange called",
            $traceId
        );

        // Tidak ada perubahan membership
        if (!array_key_exists('member_id', $data)) {
            return;
        }

        // APPLY CURRENT PERIOD
        if ($decision->effectivePeriod === DecisionResult::PERIOD_CURRENT) {
            LogHelper::logInfo(
                null,
                [
                    'member_id' => $data['member_id'] ?? null
                ],
                200,
                "MembershipChangeService::applyMembershipChange applying to CURRENT period",
                $traceId
            );

            $this->membershipResolver->applyNow(
                $customer,
                $data,
                $period,
                $billing,
                $traceId
            );

            return;
        }

        // DEFER TO NEXT PERIOD
        if ($decision->effectivePeriod === DecisionResult::PERIOD_NEXT) {

            LogHelper::logInfo(
                null,
                [
                    'member_id' => $data['member_id'] ?? null
                ],
                200,
                'MembershipChangeService::applyMembershipChange deferring to NEXT period',
                $traceId
            );

            $nextStart = Carbon::createFromFormat(
                'F Y',
                $data['billing_periode_name']
            )->startOfMonth();

            $nextEnd = $nextStart
                ->copy()
                ->endOfMonth();

            // dd($data, $nextStart, $nextEnd);

            $this->membershipResolver->scheduleNext(
                $customer,
                $data,
                $nextStart,
                $nextEnd,
                $traceId
            );
        }
    }
}
