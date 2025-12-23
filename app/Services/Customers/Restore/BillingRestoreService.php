<?php

namespace App\Services\Customers\Restore;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Billings\BillingPeriode;
use App\Models\Billings\BillingSubscriptionPeriode;
use App\Models\Billings\Billing;
use App\Models\Websystem;
use App\Services\Billings\BillingCreateService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BillingRestoreService
{
    public function __construct(
        private BillingCreateService $billingCreateService
    ) {}

    public function handle(Customer $customer): Billing
    {
        return DB::transaction(function () use ($customer) {

            /** ----------------------------------------
             * 1. Resolve active subscription
             * ---------------------------------------- */
            $subscription = CustomerSubscription::where('customer_id', $customer->id)
                ->where('status', 'active')
                ->latest()
                ->first();

            if (!$subscription) {
                throw new InvalidArgumentException('Active subscription not found');
            }

            /** ----------------------------------------
             * 2. Resolve billing period
             * ---------------------------------------- */
            $billingPeriode = $this->resolveBillingPeriode($customer);

            /** ----------------------------------------
             * 3. Resolve billing subscription periode
             * ---------------------------------------- */
            $billingSubscription = BillingSubscriptionPeriode::firstOrCreate(
                [
                    'customer_subscription_id'    => $subscription->id,
                    'billing_periode_id' => $billingPeriode->id,
                ],
                [
                    'status' => 'active',
                ]
            );

            /** ----------------------------------------
             * 4. Invalidate previous billing (safety)
             * ---------------------------------------- */
            Billing::where('billing_subscription_id', $billingSubscription->id)
                ->whereIn('status', ['BL', 'PL'])
                ->update(['status' => 'VD']); // cancelled / obsolete

            /** ----------------------------------------
             * 5. Create billing
             * ---------------------------------------- */
            $websystem = Websystem::where('slug', 'websystem')->first();

            $result = $this->billingCreateService->createBilling(
                $websystem,
                $customer,
                $billingSubscription,
                $billingPeriode,
                $subscription,
                (string) Str::uuid()
            );

            return $result['billing'];
        });
    }

    /**
     * Resolve billing periode based on membership end_date / option
     */
    private function resolveBillingPeriode(Customer $customer): BillingPeriode
    {
        // Cari membership aktif
        $membership = $customer->customerMembership()
            ->where('is_active', 1)
            ->latest()
            ->first();

        // Kalau membership one-time → end_date ada
        if ($membership && $membership->end_date) {
            $membershipEnd = Carbon::parse($membership->end_date);

            return BillingPeriode::where('status', 'active')
                ->whereDate('start_date', '<=', $membershipEnd)
                ->whereDate('end_date', '>=', $membershipEnd)
                ->firstOrFail();
        }

        // Default → periode aktif sekarang
        return BillingPeriode::where('status', 'active')
            ->orderBy('name', 'desc')
            ->firstOrFail();
    }
}
