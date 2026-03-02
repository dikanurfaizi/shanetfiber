<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Helpers\LogHelper;
use App\Http\Requests\CustomerRestoreRequest;
use App\Services\Customers\Restore\BillingRestoreService;
use App\Services\Customers\Restore\CustomerAddonRestoreService;
use App\Services\Customers\Restore\MembershipRestoreService;
use App\Services\Customers\Restore\SubscriptionRestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CustomerRestoreController extends Controller
{
    private SubscriptionRestoreService $subscriptionRestoreService;
    private CustomerAddonRestoreService $customerAddonRestoreService;
    private MembershipRestoreService $membershipRestoreService;
    private BillingRestoreService $billingRestoreService;

    public function __construct(
        SubscriptionRestoreService $subscriptionRestoreService,
        CustomerAddonRestoreService $customerAddonRestoreService,
        MembershipRestoreService $membershipRestoreService,
        BillingRestoreService $billingRestoreService
    ) {
        $this->middleware('permission:view-deleted-customer', ['only' => ['preview', 'confir']]);

        $this->subscriptionRestoreService = $subscriptionRestoreService;
        $this->customerAddonRestoreService = $customerAddonRestoreService;
        $this->membershipRestoreService = $membershipRestoreService;
        $this->billingRestoreService = $billingRestoreService;
    }

    /**
     * Preview restore workflow.
     * No mutation allowed.
     */
    public function preview(string $slug)
    {
        $traceId = (string) Str::uuid();

        LogHelper::logInfo(null, [
            'slug' => $slug,
        ], 201, 'CustomerRestoreController::preview - START', $traceId);

        $customer = Customer::onlyTrashed()
            ->where('slug', $slug)
            ->firstOrFail();

        // ==========================
        // Resolve restore context
        // ==========================
        $context = $this->resolveRestoreContext($customer);

        return view('customers.restore.preview', [
            'customer' => $customer,
            'context'  => $context,

            // billing form dependencies
            'packages' => $context->packages,
            'members'  => $context->members,
            'addons'   => $context->addons,

            'current_package_id'    => $context->current_package_id,
            'current_duration_id'   => $context->current_duration_id,
            'current_membership_id' => $context->current_membership_id,
            'billing_option'        => $context->billing_option,
            'availablePeriodes'     => $context->availablePeriodes,
            'activeCustomerAddons'  => $context->activeCustomerAddons,
            'discountEstimate'      => $context->discountEstimate,
            'current_duration_name' => $context->current_duration_name,
        ]);
    }

    /**
     * Confirm restore.
     * Actual mutation happens here.
     */
    public function confirm(CustomerRestoreRequest $request, string $slug)
    {
        $traceId = (string) Str::uuid();
        abort_if(
            $request->input('restore_intent') !== 'CONFIRM_RESTORE',
            400,
            'Invalid restore intent'
        );

        $payload = $request->payload();

        LogHelper::logInfo(null, [
            'slug' => $slug,
            'payload' => $payload,
        ], 201, 'CustomerRestoreController::confirm - START', $traceId);

        try {
            DB::transaction(function () use ($payload, $slug) {

                $customer = Customer::onlyTrashed()
                    ->where('slug', $slug)
                    ->lockForUpdate()
                    ->firstOrFail();

                LogHelper::logInfo(null, [
                    'id' => $customer->id,
                    'customer_id' => $customer->customer_id,
                    'customer_name' => $customer->name,
                    'disable_status' => $customer->disabled,
                    'activation_status' => $customer->activation,
                    'deleted_at' => $customer->deleted_at,
                    'mikrotik_id' => $customer->mikrotik_id,
                ], 200, 'Get Data Customer trashed');

                // ==========================
                // 1. Restore customer
                // ==========================
                $customer->restore();

                LogHelper::logInfo(null, ['deleted_at' => $customer->deleted_at], 200, 'Restore Customer');

                // ==========================
                // 2. Resolve subscription
                // ==========================
                $subscription = $this->subscriptionRestoreService->handle($customer, $payload);

                LogHelper::logInfo(null, ['subscription_id' => $subscription->id], 200, 'SubscriptionRestoreService STEP');

                // ==========================
                // 3. Resolve membership
                // ==========================
                $this->membershipRestoreService->handle($customer, $payload);

                LogHelper::logInfo(null, [], 200, 'MembershipRestoreService STEP');

                // ==========================
                // 4. Resolve addons
                // ==========================
                $this->customerAddonRestoreService->handle($customer, $payload['addons'] ?? []);

                LogHelper::logInfo(null, [], 200, 'CustomerAddonRestoreService STEP');

                // ==========================
                // 4. Regenerate billing
                // ==========================
                $this->billingRestoreService->handle($customer);
                LogHelper::logInfo(null, [], 200, 'Regerenerat Billing STEP');
            });

            LogHelper::logWarning(null, [
                'slug' => $slug,
            ], 200, 'CustomerRestoreController::confirm - SUCCESS', $traceId);

            return redirect()
                ->route('customers.show', [$slug, 'tab' => 'billing'])
                ->with('success', 'Customer restored and billing regenerated.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Resolve restore context (READ-ONLY).
     * No mutation here.
     */
    private function resolveRestoreContext(Customer $customer)
    {
        $subscription = CustomerSubscription::where('customer_id', $customer->id)
            ->latest()
            ->first();

        $mode = match (true) {
            !$subscription => 'NO_SUBSCRIPTION',
            $subscription->isExpired() => 'EXPIRED',
            default => 'EXISTING',
        };

        return app('App\Services\Customers\Restore\RestoreContextResolver')
            ->resolve($customer, $subscription, $mode);
    }
}
