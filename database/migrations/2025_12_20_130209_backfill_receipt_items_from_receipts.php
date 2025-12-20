<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {

            $receipts = DB::table('receipts')
                ->whereNotNull('billing_payment_id')
                ->get();

            foreach ($receipts as $receipt) {

                // Ambil billing payment
                $billingPayment = DB::table('billing_payments')
                    ->where('id', $receipt->billing_payment_id)
                    ->first();

                if (!$billingPayment) {
                    continue; // data kotor, skip
                }

                $billing = DB::table('billings')
                    ->where('id', $billingPayment->billing_id)
                    ->first();

                $billingSubscriptionPeriode = DB::table('billing_subscription_periodes')
                    ->where('id', $billing->billing_subscription_id)
                    ->first();

                $customerSubscription = DB::table('customer_subscriptions')
                    ->where('id', $billingSubscriptionPeriode->customer_subscription_id)
                    ->first();

                // Ambil package
                $package = DB::table('packages')
                    ->where('id', $customerSubscription->package_id)
                    ->first();

                if (!$package) {
                    continue; // skip jika package tidak ada
                }

                // Cegah double insert
                $exists = DB::table('receipt_items')
                    ->where('receipt_id', $receipt->id)
                    ->where('type', 'package')
                    ->exists();

                if ($exists) {
                    continue;
                }

                $qty = 1;
                $unitPrice = (float) $customerSubscription->price_final;
                $subtotal  = $qty * $unitPrice;

                DB::table('receipt_items')->insert([
                    'receipt_id' => $receipt->id,
                    'type'       => 'package',
                    'ref_id'     => $package->id,
                    'name'       => $package->name,
                    'qty'        => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal'   => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // rollback aman: hanya hapus receipt_items hasil backfill
        DB::table('receipt_items')
            ->where('type', 'package')
            ->delete();
    }
};
