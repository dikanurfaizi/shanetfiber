<?php

namespace App\Services\Billings;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerAddon;
use App\Models\Customers\CustomerSubscription;
use App\Models\Billings\BillingSubscriptionPeriode;
use App\Models\Billings\BillingPeriode;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingItem;
use App\Models\Membership\CustomerMembership;
use App\Models\Websystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Helpers\LogHelper;
use App\Helpers\BillingDateResolver;
use App\Helpers\ExecutionTimer;
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
        string $traceId
    ): array {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(
            null,
            [],
            201,
            'Create Billing Started',
            $traceId,
            $timer->lap()
        );

        $billing =  DB::transaction(function () use (
            $websystem,
            $customer,
            $billingSubscription,
            $billingPeriode,
            $customerSubscription,
            $traceId,
            &$timer
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
            $yearMonth = Carbon::parse($billingPeriode->name)->format('Ym');


            /** ----------------------------------------------------
             * 3. Generate Invoice Sequence (per customer, per month)
             *    SAFE WITH LOCKING → to prevent race condition
             * ---------------------------------------------------- */
            $seq = Billing::where('customer_id', $customer->id)
                ->whereHas('billingSubscription', function ($q) use ($billingPeriode) {
                    $q->where('billing_periode_id', $billingPeriode->id);
                })
                ->lockForUpdate()
                ->count() + 1;

            $seqFormatted = str_pad($seq, 4, '0', STR_PAD_LEFT);

            $billingNumber = "INV-{$yearMonth}-{$customer->id}-{$seqFormatted}";

            $userId = auth()->id() ?? User::orderBy('id', 'asc')->value('id');

            /** ----------------------------------------------------
             * 4. Create Billing Header
             * ---------------------------------------------------- */
            $billing = Billing::create([
                'slug' => Str::slug($customer->id . '-' . Str::random(4)),
                'billing_number' => $billingNumber,
                'customer_id' => $customer->id,
                'billing_subscription_id' => $billingSubscription->id,
                'base_price' => $customerSubscription->package->price,
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'status' => 'BL',
                'created_by' => $userId,
                'updated_by' => $userId,
                'due_date' => $dueDate,
            ]);


            /** ----------------------------------------------------
             * 5. Add Billing Items: PACKAGE
             * ---------------------------------------------------- */
            $item = [
                'billing_id' => $billing->id,
                'item_type' => 'package',
                'source_type' => 'package',
                'source_id' => $customerSubscription->package_id,
                'item_name' => $customerSubscription->package->name,
                'qty' => 1,
                'price'    => $customerSubscription->package->price,
                'subtotal' => $customerSubscription->package->price,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $items[] = $item;


            /** ----------------------------------------------------
             * 6. Add Discount Item (if exists)
             * ---------------------------------------------------- */
            if ($customerSubscription->discount_type_final !== 'none') {

                $discountLabel = $customerSubscription->discount_type_final === 'percent'
                    ? "{$customerSubscription->discount_value_final}% Discount"
                    : "Discount";

                $discountAmount = $customerSubscription->discount_type_final === 'percent'
                    ? ($customerSubscription->discount_value_final / 100) * $customerSubscription->price_final
                    : $customerSubscription->discount_value_final;

                $discountAmount = -abs($discountAmount);

                $item = [
                    'billing_id' => $billing->id,
                    'item_type' => 'discount',
                    'source_type' => 'package_duration',
                    'source_id' => $customerSubscription->package_duration_id,
                    'item_name' => $discountLabel,
                    'qty' => 1,
                    'price' => $discountAmount,
                    'subtotal' => $discountAmount,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $items[] = $item;
            }

            /** ----------------------------------------
             * 7. Billing Items – MEMBERSHIP DISCOUNT
             * ---------------------------------------- */
            $periodStart = $billingPeriode->startDate();
            $periodEnd   = $billingPeriode->endDate();

            $membership = CustomerMembership::with('membership')
                ->where('customer_id', $customer->id)
                ->where('is_active', 1)
                ->where('start_date', '<=', $periodEnd)
                ->where(function ($q) use ($periodStart) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $periodStart);
                })
                ->latest('start_date')
                ->first();

            if ($membership?->membership) {

                $base = $customerSubscription->price_final;

                $discount = match ($membership->membership->discount_type) {
                    'percent' => ($membership->membership->discount_value / 100) * $base,
                    'amount'  => $membership->membership->discount_value,
                    default   => 0,
                };

                if ($discount > 0) {
                    $item = [
                        'billing_id' => $billing->id,
                        'item_type'  => 'discount',
                        'source_type' => 'membership',
                        'source_id'  => $membership->membership->id,
                        'item_name'  => $membership->membership->name,
                        'qty'        => 1,
                        'price'      => -abs($discount),
                        'subtotal'   => -abs($discount),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $items[] = $item;
                }
            }


            /** ----------------------------------------------------
             * 8. Add Addons (customer_addons)
             * ---------------------------------------------------- */
            $addons = CustomerAddon::with('addon')
                ->where('customer_id', $customer->id)
                ->where('is_active', 1)
                ->get();

            foreach ($addons as $addon) {
                $item = [
                    'billing_id' => $billing->id,
                    'item_type' => 'addon',
                    'source_type' => 'addon',
                    'source_id' => $addon->addon_id,
                    'item_name' => $addon->addon->name,
                    'qty' => $addon->qty,
                    'price' => $addon->unit_price,
                    'subtotal' => $addon->qty * $addon->unit_price,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $items[] = $item;
            }

            BillingItem::insert($items);


            /** ----------------------------------------------------
             * 9. Recalculate Totals (Fixed for FREE package)
             * ---------------------------------------------------- */

            // 1. Subtotal hanya dari item yang ditagihkan
            $chargeableSubtotal = collect($items)
                ->whereIn('item_type', ['package', 'addon'])
                ->sum('subtotal');

            // 2. Discount (biasanya negatif)
            $discountAmount = collect($items)
                ->where('item_type', 'discount')
                ->sum('subtotal');

            // 3. Pajak hanya dari chargeable subtotal
            $tax = $this->calculateTax($websystem, $chargeableSubtotal);

            // 4. Total akhir
            $total = max(0, $chargeableSubtotal + $discountAmount + $tax);

            // 5. Default status
            $status = 'BL';

            // 6. Auto paid jika benar-benar gratis
            if ($total <= 0) {
                $status = 'LS';
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

            // 7. Update billing
            $billing->update([
                'subtotal'         => $chargeableSubtotal,
                'tax_amount'       => $tax,
                'discount_amount'  => $discountAmount,
                'total_amount'     => $total,
                'status'           => $status,
            ]);

            /** ----------------------------------------------------
             * 10. Logging
             * ---------------------------------------------------- */
            LogHelper::logInfo(
                null,
                [
                    'billing_id' => $billing->id,
                    'billing_number' => $billing->billing_number,
                    'customer_id' => $customer->id,
                    'amount' => $total
                ],
                201,
                'Billing created successfully',
                $traceId,
                $timer->lap()
            );

            return $billing;
        });

        return [
            'success'        => true,
            'billing_id'     => $billing->id,
            'billing'        => $billing,
        ];
    }

    public function calculateTax($websystem, float $subtotal): float
    {
        if (!$websystem || !$websystem->enable_tax) {
            return 0;
        }

        $rate = (float) $websystem->tax_rate;
        $rate = $rate > 1 ? $rate / 100 : $rate;

        return round($subtotal * $rate, 0);
    }
}
