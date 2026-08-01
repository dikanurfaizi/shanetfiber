<?php

namespace App\Services\Billings;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerAddon;
use App\Models\Customers\CustomerSubscription;
use App\Models\Billings\BillingSubscriptionPeriode;
use App\Models\Billings\BillingPeriode;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingItem;
use App\Models\Websystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\LogHelper;
use App\Helpers\BillingDateResolver;
use App\Helpers\SubscriptionPricingCalculator;
use App\Models\Billings\BillingPayment;
use App\Models\User;
use App\Services\Receipts\CreateReceiptService;
use App\Services\Receipts\ReferenceIdGeneratorService;

class BillingCreateService
{
    public function __construct(
        private CreateReceiptService $receiptService,
        private ReferenceIdGeneratorService $referenceIdGeneratorService
    ) {}

    public function createBilling(
        Websystem $websystem,
        Customer $customer,
        BillingSubscriptionPeriode $billingSubscription,
        BillingPeriode $billingPeriode,
        CustomerSubscription $customerSubscription,
        string $traceId,
    ): array {
        LogHelper::logInfo(
            null,
            [],
            201,
            'Create Billing Started',
            $traceId
        );

        $billing = DB::transaction(function () use (
            $websystem,
            $customer,
            $billingSubscription,
            $billingPeriode,
            $customerSubscription,
            $traceId
        ) {
            /** ----------------------------------------
             * 1. Resolve Issue Date & Due Date
             * ---------------------------------------- */
            $issueDate = now();
            $dueDate   = app(BillingDateResolver::class)
                ->resolveDueDate($billingPeriode, $issueDate);

            /** ----------------------------------------------------
             * 2. Prepare Year-Month
             * ---------------------------------------------------- */
            $yearMonth = $billingPeriode->year;

            /** ----------------------------------------------------
             * 3. Generate Invoice Sequence
             * ---------------------------------------------------- */
            $seq = Billing::where('customer_id', $customer->id)
                ->whereHas('billingSubscription', function ($q) use ($billingPeriode) {
                    $q->where('billing_periode_id', $billingPeriode->id);
                })
                ->lockForUpdate()
                ->count() + 1;

            $seqFormatted  = str_pad($seq, 4, '0', STR_PAD_LEFT);
            $billingNumber = "INV-{$yearMonth}-{$customer->id}-{$seqFormatted}";
            $userId        = auth()->id() ?? User::orderBy('id', 'asc')->value('id');

            /** ----------------------------------------------------
             * 4. Create Billing Header
             * ---------------------------------------------------- */
            $billing = Billing::create([
                'slug'                    => Str::slug($customer->id . '-' . Str::random(4)),
                'billing_number'          => $billingNumber,
                'customer_id'             => $customer->id,
                'billing_subscription_id' => $billingSubscription->id,
                'base_price'              => $customerSubscription->package->price,
                'subtotal'                => 0,
                'tax_amount'              => 0,
                'total_amount'            => 0,
                'status'                  => 'BL',
                'created_by'              => $userId,
                'updated_by'              => $userId,
                'due_date'                => $dueDate,
            ]);

            $items = [];

            /** ----------------------------------------------------
             * 5. Add Billing Items: PACKAGE
             * ---------------------------------------------------- */
            $items[] = [
                'billing_id'  => $billing->id,
                'item_type'   => 'package',
                'source_type' => 'package',
                'source_id'   => $customerSubscription->package_id,
                'item_name'   => $customerSubscription->package->name,
                'qty'         => 1,
                'price'       => $customerSubscription->price_final,
                'subtotal'    => $customerSubscription->price_final,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            /** ----------------------------------------------------
             * 6. Add Billing Items: MEMBERSHIP DISCOUNT (💡 Satu Pintu via Helper)
             * ---------------------------------------------------- */
            $membershipDiscount = SubscriptionPricingCalculator::resolveMembershipDiscount(
                customerId: $customer->id,
                basePriceForDiscount: (float) $customerSubscription->price_final,
                periodStart: $billingPeriode->startDate(),
                periodEnd: $billingPeriode->endDate()
            );

            if ($membershipDiscount) {
                $discountVal = -abs($membershipDiscount['discount_amount']);
                $items[] = [
                    'billing_id'  => $billing->id,
                    'item_type'   => 'discount',
                    'source_type' => 'membership',
                    'source_id'   => $membershipDiscount['membership_id'],
                    'item_name'   => $membershipDiscount['item_name'],
                    'qty'         => 1,
                    'price'       => $discountVal,
                    'subtotal'    => $discountVal,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            /** ----------------------------------------------------
             * 7. Add Addons (customer_addons)
             * ---------------------------------------------------- */
            $addons = CustomerAddon::with('addon')
                ->where('customer_id', $customer->id)
                ->where('is_active', 1)
                ->get();

            foreach ($addons as $addon) {
                $items[] = [
                    'billing_id'  => $billing->id,
                    'item_type'   => 'addon',
                    'source_type' => 'addon',
                    'source_id'   => $addon->addon_id,
                    'item_name'   => $addon->addon->name,
                    'qty'         => $addon->qty,
                    'price'       => $addon->unit_price,
                    'subtotal'    => $addon->qty * $addon->unit_price,
                    'created_at'  => now(),
                    'updated_at'  => now()
                ];
            }

            BillingItem::insert($items);

            /** ----------------------------------------------------
             * 8. Recalculate Totals
             * ---------------------------------------------------- */
            $chargeableSubtotal = collect($items)
                ->whereIn('item_type', ['package', 'addon'])
                ->sum('subtotal');

            $discountAmount = collect($items)
                ->where('item_type', 'discount')
                ->sum('subtotal');

            $tax   = $this->calculateTax($websystem, $chargeableSubtotal);
            $total = max(0, $chargeableSubtotal + $discountAmount + $tax);

            $status = 'BL';

            if ($total <= 0) {
                $status        = 'LS';
                $paymentMethod = 'cash';

                $billingPayment = BillingPayment::create([
                    'billing_id'      => $billing->id,
                    'amount_paid'     => 0,
                    'payment_method'  => $paymentMethod,
                    'reference_id'    => $this->referenceIdGeneratorService->generate('ACT'),
                    'paid_by_user_id' => $userId,
                    'payment_time'    => now(),
                ]);

                $this->receiptService->createReceipt(
                    $customerSubscription,
                    $billing,
                    $billingPayment,
                    ['payment_method' => $paymentMethod],
                    $traceId
                );
            }

            $billing->update([
                'subtotal'        => $chargeableSubtotal,
                'tax_amount'      => $tax,
                'discount_amount' => $discountAmount,
                'total_amount'    => $total,
                'status'          => $status,
            ]);

            /** ----------------------------------------------------
             * 9. Logging
             * ---------------------------------------------------- */
            LogHelper::logInfo(
                null,
                [
                    'billing_id'     => $billing->id,
                    'billing_number' => $billing->billing_number,
                    'customer_id'    => $customer->id,
                    'amount'         => $total
                ],
                201,
                'Billing created successfully',
                $traceId
            );

            return $billing;
        });

        return [
            'success'    => true,
            'billing_id' => $billing->id,
            'billing'    => $billing,
        ];
    }

    public function calculateTax(Websystem $websystem, float $subtotal): float
    {
        if (!$websystem || !$websystem->enable_tax) {
            return 0;
        }

        $rate = (float) $websystem->tax_rate;
        $rate = $rate > 1 ? $rate / 100 : $rate;

        return round($subtotal * $rate, 0);
    }
}
