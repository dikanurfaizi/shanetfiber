<?php

namespace App\Services\Customers\Billing;

use App\Domain\Decision\DecisionResult;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingItem;
use App\Models\Billings\BillingPeriode;
use App\Models\Billings\BillingSubscriptionPeriode;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerAddon;
use App\Models\Customers\CustomerSubscription;
use App\Models\Membership\CustomerMembership;
use App\Models\Websystem;
use App\Services\Billings\BillingCreateService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Str;

class ProratedPackageBillingService
{
    public function __construct(
        private BillingCreateService $billingCreateService,
        private DaySegmentsService $daySegments,
        private ProratedAmountsService $amountsService
    ) {}

    public function applyUpgrade(
        Customer $customer,
        Billing $billing,
        BillingPeriode $billingPeriode,
        BillingSubscriptionPeriode $billingSubPeriode,
        CustomerSubscription $oldSubscription,
        CustomerSubscription $newSubscription,
        Carbon $changeDate,
        string $prorateMode,
        Websystem $websystem,
        string $traceId
    ): void {

        // ======================================================
        // 1. GUARD — downgrade only blocked if prorateMode is NOT accumulative
        // ======================================================
        if (
            $newSubscription->package->price < $oldSubscription->package->price
            && $prorateMode !== DecisionResult::PRORATE_ACCUMULATIVE
        ) {
            throw new DomainException(
                'Downgrade only allowed for next billing period'
            );
        }

        // ======================================================
        // 2. GUARD — double change
        // ======================================================
        if ($billingSubPeriode->package_changed_at !== null) {
            throw new DomainException(
                'Package already changed in this billing period'
            );
        }

        // ======================================================
        // 3. Day segments
        // ======================================================
        $segments = $this->daySegments->calculate(
            $billingPeriode,
            $changeDate
        );

        // ======================================================
        // 4. Prorated amounts
        // ======================================================
        if ($prorateMode === DecisionResult::PRORATE_ACCUMULATIVE) {

            // MODEL A — belum bayar
            $oldUsed = ($segments['old'] / $segments['total'])
                * $oldSubscription->package->price;

            $newUsed = ($segments['new'] / $segments['total'])
                * $newSubscription->package->price;

            $rawAmountToBill = $oldUsed + $newUsed;
        } else {

            // MODEL B — sudah bayar
            $unusedOld = ($segments['new'] / $segments['total'])
                * $oldSubscription->package->price;

            $rawAmountToBill = max(
                ($segments['new'] / $segments['total'])
                    * $newSubscription->package->price
                    - $unusedOld,
                0
            );
        }

        $amountToBill = $this->roundToHundreds($rawAmountToBill);

        // ======================================================
        // 5. UPDATE billing_subscription_periodes (audit anchor)
        // ======================================================
        $billingSubPeriode->update([
            'customer_subscription_id' => $newSubscription->id,
            'package_id_before'        => $oldSubscription->package_id,
            'package_id_after'         => $newSubscription->package_id,
            'package_changed_at'       => $changeDate->toDateString(),
            'status'                   => 'active',
        ]);

        LogHelper::logInfo(
            null,
            [
                'customer_subscription_id'        => $newSubscription->id,
                'billing_subscription_periode_id' => $billingSubPeriode->id,
                'package_id_before'               => $oldSubscription->package_id,
                'package_id_after'                => $newSubscription->package_id,
                'package_changed_at'              => $changeDate->toDateString(),
            ],
            200,
            'Billing subscription periode updated with package change info',
            $traceId
        );

        // ======================================================
        // 6. RESET billing items (PACKAGE only)
        // ======================================================
        BillingItem::where('billing_id', $billing->id)
            ->where('item_type', 'package')
            ->delete();

        BillingItem::create([
            'billing_id'  => $billing->id,
            'item_type'   => 'package',
            'source_type' => 'package',
            'source_id'   => $newSubscription->package_id,
            'item_name'   => $newSubscription->package->name,
            'qty'         => 1,
            'price'       => $amountToBill,
            'subtotal'    => $amountToBill,
        ]);

        // ======================================================
        // 7. RECALC billing header
        // ======================================================
        $subtotal = BillingItem::where('billing_id', $billing->id)
            ->sum('subtotal');

        $tax = $this->billingCreateService
            ->calculateTax($websystem, $subtotal);

        $billing->update([
            'subtotal'     => $subtotal,
            'tax_amount'   => $tax,
            'total_amount' => $subtotal + $tax,
            'due_date'     => $billingPeriode->start_date->copy()->day(15),
        ]);

        // ======================================================
        // 8. AUDIT LOG (JSON payload)
        // ======================================================
        LogHelper::logInfo(
            null,
            [
                'customer_id'      => $customer->id,
                'billing_id'       => $billing->id,
                'change_date'      => $changeDate->toDateString(),
                'segments'         => $segments,
                'old_price'        => $oldSubscription->package->price,
                'new_price'        => $newSubscription->package->price,
                'raw_amount_billed' => $rawAmountToBill,
                'amount_billed'    => $amountToBill,
            ],
            200,
            'Package upgraded with prorated billing (upgrade-only)',
            $traceId
        );
    }

    private function roundToHundreds(float $amount): float
    {
        return round($amount / 100) * 100;
    }
}
