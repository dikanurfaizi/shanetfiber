<?php

namespace App\Services\Customers\Billing;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\CustomerSubscription;
use App\Models\Membership\CustomerMembership;
use App\Models\Packages\Package;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Str;

class BillingRuleGuardService
{
    public function canChangePackage(?Billing $billing): array
    {
        if (!$billing) {
            return [false, 'Billing not found for current period'];
        }

        if (!in_array($billing->status, ['LS', 'VD'], true)) {
            return [false, 'Package change only allowed when billing is settled/marked'];
        }

        // Cek sudah pernah ubah package di period ini
        // (lebih aman cek dari billing_subscription_periodes untuk period aktif)
        if ($billing->billingSubscription && $billing->billingSubscription->package_changed_at) {
            return [false, 'Package change only allowed 1 time in a month'];
        }

        return [true, null];
    }

    public function isSamePackage(?CustomerSubscription $sub, int $packageId): bool
    {
        return $sub && (int)$sub->package_id === (int)$packageId;
    }

    public function canChangeMembership(
        ?Billing $billing,
        int $memberId,
        string $billingOption,
        string $periodeScope,
        string $traceId
    ): array {
        $timer = new ExecutionTimer();

        $contextInput = [
            'billing_id'     => $billing?->id,
            'billing_status' => $billing?->status,
            'member_id'      => $memberId,
            'billing_option' => $billingOption,
            'periode_scope'  => $periodeScope,
        ];

        // REMOVE MEMBERSHIP → ALWAYS OK
        if ($memberId === 0) {
            LogHelper::logInfo(null, $contextInput, 200, "[Membership] Action: Remove membership triggered (Always Allowed)", $traceId, $timer->lap());
            return [true, 'remove', false, null];
        }

        if (!$billing) {
            LogHelper::logWarning(null, $contextInput, 404, "[Membership] Reject: Billing data not found", $traceId, $timer->lap());
            return [false, null, false, 'Billing not found'];
        }

        // JIKA BILLING SUDAH LUNAS / SETTLED (LS = Lunas, VD = Void/Selesai)
        if (in_array($billing->status, ['LS', 'VD'], true)) {

            if ($billingOption === 'monthly') {
                LogHelper::logInfo(null, $contextInput, 200, "[Membership] Allow: Monthly change on settled billing (Applied to current)", $traceId, $timer->lap());
                return [true, 'current', false, null];
            }

            if ($billingOption === 'one_time') {
                if ($periodeScope === 'current') {
                    $reason = 'One time membership cannot be applied to current settled billing';

                    LogHelper::logWarning(null, array_merge($contextInput, ['reason' => $reason]), 422, "[Membership] Reject: One-time scope current on settled billing", $traceId, $timer->lap());

                    return [false, null, false, $reason];
                }

                LogHelper::logInfo(null, $contextInput, 200, "[Membership] Allow: One-time change on settled billing (Applied to next)", $traceId, $timer->lap());
                return [true, 'next', false, null];
            }
        }

        // JIKA BILLING BELUM LUNAS / BELUM SETTLED
        if (!in_array($billing->status, ['LS', 'VD'], true)) {

            if ($periodeScope === 'current') {
                LogHelper::logInfo(null, $contextInput, 200, "[Membership] Allow: Change on unsettled billing (Applied to current, triggers recalculate)", $traceId, $timer->lap());
                return [true, 'current', true, null];
            }

            LogHelper::logInfo(null, $contextInput, 200, "[Membership] Allow: Change on unsettled billing (Applied to next)", $traceId, $timer->lap());
            return [true, 'next', false, null];
        }

        // FALLBACK (Jika ada kondisi status/kombinasi yang lolos dari pengecekan di atas)
        $reason = 'Membership rule not matched';
        LogHelper::logError(null, array_merge($contextInput, ['reason' => $reason]), 500, "[Membership] Critical: System reached fallback block without matching any business rule", $traceId);

        return [false, null, false, $reason];
    }

    public function isSamePeriod(string $billingPeriodeName, BillingPeriode $currentPeriod): bool
    {
        $frontend = Carbon::createFromFormat('F Y', $billingPeriodeName);
        return $frontend->format('Y-m') === $currentPeriod->start_date->format('Y-m');
    }
}
