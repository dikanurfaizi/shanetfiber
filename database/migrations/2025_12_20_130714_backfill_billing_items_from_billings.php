<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {

            $billings = DB::table('billings')
                ->whereNotNull('billing_subscription_id')
                ->get();

            foreach ($billings as $billing) {
                $billingSubscriptionPeriode = DB::table('billing_subscription_periodes')
                    ->where('id', $billing->billing_subscription_id)
                    ->first();

                if (!$billingSubscriptionPeriode) {
                    continue; // skip jika billing subscription periode tidak ada
                }

                $customerSubscription = DB::table('customer_subscriptions')
                    ->where('id', $billingSubscriptionPeriode->customer_subscription_id)
                    ->first();

                if (!$customerSubscription) {
                    continue; // skip jika customer subscription tidak ada
                }

                // Ambil package
                $package = DB::table('packages')
                    ->where('id', $customerSubscription->package_id)
                    ->first();

                if (!$package) {
                    continue; // skip jika package tidak ada
                }

                // Cegah double insert
                $exists = DB::table('billing_items')
                    ->where('billing_id', $billing->id)
                    ->where('item_type', 'package')
                    ->exists();

                if ($exists) {
                    continue;
                }

                $qty = 1;
                $unitPrice = (float) $customerSubscription->price_final;
                $subtotal  = $qty * $unitPrice;

                DB::table('billing_items')->insert([
                    'billing_id' => $billing->id,
                    'item_type'  => 'package',
                    'source_type' => 'package',
                    'source_id'     => $package->id,
                    'item_name'       => $package->name,
                    'qty'        => $qty,
                    'price' => $unitPrice,
                    'subtotal'   => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // rollback aman: hanya hapus billing_items hasil backfill
        DB::table('billing_items')
            ->where('item_type', 'package')
            ->delete();
    }
};
