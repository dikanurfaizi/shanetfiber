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
use App\Helpers\LogHelper;

class BillingRestoreService
{
    public function __construct(
        private BillingCreateService $billingCreateService
    ) {}

    public function handle(Customer $customer): Billing
    {
        LogHelper::logInfo(
            null,
            [
                'customer_id' => $customer->id,
                'slug'        => $customer->slug,
            ],
            200,
            'BillingRestoreService::handle - START'
        );

        return DB::transaction(function () use ($customer) {

            /** ----------------------------------------
             * 1. Resolve active subscription
             * ---------------------------------------- */
            $subscription = CustomerSubscription::where('customer_id', $customer->id)
                ->where('status', 'active')
                ->latest()
                ->first();

            if (!$subscription) {
                LogHelper::logError(
                    null,
                    ['customer_id' => $customer->id],
                    404,
                    'BillingRestoreService::handle - ACTIVE SUBSCRIPTION NOT FOUND'
                );
                throw new InvalidArgumentException('Active subscription not found');
            }

            LogHelper::logInfo(
                null,
                [
                    'subscription_id' => $subscription->id,
                    'package_id'      => $subscription->package_id,
                ],
                200,
                'BillingRestoreService::handle - ACTIVE SUBSCRIPTION FOUND'
            );

            /** ----------------------------------------
             * 2. Resolve billing period
             * ---------------------------------------- */
            $billingPeriode = $this->resolveBillingPeriode($customer);

            LogHelper::logInfo(
                null,
                [
                    'billing_periode_id' => $billingPeriode->id,
                    'periode_name'       => $billingPeriode->name,
                    'start_date'         => $billingPeriode->start_date,
                    'end_date'           => $billingPeriode->end_date,
                ],
                200,
                'BillingRestoreService::handle - BILLING PERIODE RESOLVED'
            );

            /** ----------------------------------------
             * 3. Resolve billing subscription periode
             * ---------------------------------------- */
            $billingSubscription = BillingSubscriptionPeriode::firstOrCreate(
                [
                    'customer_subscription_id' => $subscription->id,
                    'billing_periode_id'       => $billingPeriode->id,
                ],
                [
                    'status' => 'active',
                ]
            );

            LogHelper::logInfo(
                null,
                [
                    'billing_subscription_id' => $billingSubscription->id,
                    'was_recently_created'    => $billingSubscription->wasRecentlyCreated,
                ],
                200,
                'BillingRestoreService::handle - BILLING SUBSCRIPTION PERIODE READY'
            );

            /** ----------------------------------------
             * 4. Invalidate previous billing (safety)
             * ---------------------------------------- */
            $invalidated = Billing::where('billing_subscription_id', $billingSubscription->id)
                ->whereIn('status', ['BL', 'PL'])
                ->update(['status' => 'VD']);

            LogHelper::logInfo(
                null,
                [
                    'billing_subscription_id' => $billingSubscription->id,
                    'affected_rows'           => $invalidated,
                ],
                200,
                'BillingRestoreService::handle - PREVIOUS BILLING INVALIDATED'
            );

            /** ----------------------------------------
             * 5. Create billing
             * ---------------------------------------- */
            $websystem = Websystem::where('slug', 'websystem')->first();

            if (!$websystem) {
                LogHelper::logError(
                    null,
                    ['slug' => 'websystem'],
                    404,
                    'BillingRestoreService::handle - WEBSYSTEM NOT FOUND'
                );
                throw new InvalidArgumentException('Websystem not found');
            }

            LogHelper::logInfo(
                null,
                [
                    'customer_id'             => $customer->id,
                    'billing_subscription_id' => $billingSubscription->id,
                    'billing_periode_id'      => $billingPeriode->id,
                ],
                200,
                'BillingRestoreService::handle - CREATE BILLING START'
            );

            $result = $this->billingCreateService->createBilling(
                $websystem,
                $customer,
                $billingSubscription,
                $billingPeriode,
                $subscription,
                (string) Str::uuid()
            );

            LogHelper::logInfo(
                null,
                [
                    'billing_id'     => $result['billing']->id ?? null,
                    'billing_number' => $result['billing']->billing_number ?? null,
                ],
                200,
                'BillingRestoreService::handle - CREATE BILLING SUCCESS'
            );

            return $result['billing'];
        });
    }

    /**
     * Resolve billing periode based on membership end_date / option
     */
    private function resolveBillingPeriode(Customer $customer): BillingPeriode
    {
        LogHelper::logInfo(
            null,
            ['customer_id' => $customer->id],
            200,
            'BillingRestoreService::resolveBillingPeriode - START'
        );

        $membership = $customer->customerMembership()
            ->where('is_active', 1)
            ->latest()
            ->first();

        if ($membership) {
            LogHelper::logInfo(
                null,
                [
                    'membership_id' => $membership->id,
                    'end_date'      => $membership->end_date,
                ],
                200,
                'BillingRestoreService::resolveBillingPeriode - ACTIVE MEMBERSHIP FOUND'
            );
        } else {
            LogHelper::logInfo(
                null,
                ['customer_id' => $customer->id],
                201,
                'BillingRestoreService::resolveBillingPeriode - NO ACTIVE MEMBERSHIP'
            );
        }

        // One-time membership → gunakan end_date
        if ($membership && $membership->end_date) {
            $membershipEnd = Carbon::parse($membership->end_date);

            $periode = BillingPeriode::where('status', 'active')
                ->whereDate('start_date', '<=', $membershipEnd)
                ->whereDate('end_date', '>=', $membershipEnd)
                ->firstOrFail();

            LogHelper::logInfo(
                null,
                [
                    'billing_periode_id' => $periode->id,
                    'periode_name'       => $periode->name,
                ],
                200,
                'BillingRestoreService::resolveBillingPeriode - PERIODE FROM MEMBERSHIP'
            );

            return $periode;
        }

        // Default → periode aktif sekarang
        $periode = BillingPeriode::where('status', 'active')
            ->orderBy('name', 'desc')
            ->firstOrFail();

        LogHelper::logInfo(
            null,
            [
                'billing_periode_id' => $periode->id,
                'periode_name'       => $periode->name,
            ],
            200,
            'BillingRestoreService::resolveBillingPeriode - DEFAULT ACTIVE PERIODE'
        );

        return $periode;
    }
}
