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
    public function __construct()
    {
        $this->middleware('permission:view-deleted-customer', ['only' => ['preview', 'confir']]);
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

        LogHelper::logWarning(null, [
            'slug' => $slug,
        ], 201, 'CustomerRestoreController::confirm - START', $traceId);

        try {
            DB::transaction(function () use ($payload, $slug) {

                $customer = Customer::onlyTrashed()
                    ->where('slug', $slug)
                    ->lockForUpdate()
                    ->firstOrFail();

                // ==========================
                // 1. Restore customer
                // ==========================
                $customer->restore();

                // ==========================
                // 2. Resolve subscription
                // ==========================
                app(SubscriptionRestoreService::class)
                    ->handle($customer, $payload);

                // ==========================
                // 3. Resolve membership
                // ==========================
                app(MembershipRestoreService::class)
                    ->handle($customer, $payload);

                // ==========================
                // 4. Resolve addons
                // ==========================
                app(CustomerAddonRestoreService::class)
                    ->handle($customer, $payload['addons'] ?? []);

                // ==========================
                // 4. Regenerate billing
                // ==========================
                app(BillingRestoreService::class)
                    ->handle($customer);
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
