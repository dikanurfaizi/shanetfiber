<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration {

    public function up(): void
    {
        DB::transaction(function () {

            $targetMinimalPeriode = 'December 2025';

            $memberships = DB::table('customer_memberships')
                ->join('memberships', 'memberships.id', '=', 'customer_memberships.membership_id')
                ->select(
                    'customer_memberships.id as cm_id',
                    'customer_memberships.customer_id',
                    'customer_memberships.start_date',
                    'customer_memberships.end_date',
                    'memberships.name as membership_name',
                    'memberships.discount_value'
                )
                ->get();

            foreach ($memberships as $cm) {

                // ==========================
                // Resolve target periode name
                // ==========================
                if ($cm->end_date === null) {
                    $periodeName = $targetMinimalPeriode;
                } else {
                    $periodeName = Carbon::parse($cm->end_date)->format('F Y');
                }

                // ==========================
                // Resolve billing_periode
                // ==========================
                $billingPeriode = DB::table('billing_periodes')
                    ->where('name', $periodeName)
                    ->first();

                if (!$billingPeriode) {
                    continue; // skip kalau periode tidak ada
                }

                // ==========================
                // Resolve billing
                // ==========================
                $billing = DB::table('billings')
                    ->where('customer_id', $cm->customer_id)
                    ->whereExists(function ($q) use ($billingPeriode) {
                        $q->select(DB::raw(1))
                            ->from('billing_subscription_periodes')
                            ->whereColumn('billing_subscription_periodes.id', 'billings.billing_subscription_id')
                            ->where('billing_subscription_periodes.billing_periode_id', $billingPeriode->id);
                    })
                    ->first();

                if (!$billing) {
                    continue; // skip kalau billing belum ada
                }

                // ==========================
                // Prevent double insert
                // ==========================
                $exists = DB::table('billing_items')
                    ->where('billing_id', $billing->id)
                    ->where('item_type', 'discount')
                    ->where('source_type', 'membership')
                    ->where('source_id', $cm->cm_id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // ==========================
                // Insert billing_item
                // ==========================
                DB::table('billing_items')->insert([
                    'billing_id'   => $billing->id,
                    'item_type'    => 'discount',
                    'item_name'    => $cm->membership_name,
                    'qty'          => 1,
                    'price'   => -abs($cm->discount_value),
                    'subtotal'     => -abs($cm->discount_value),
                    'source_type'  => 'membership',
                    'source_id'    => $cm->cm_id,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // intentionally left blank
        // backfill migration should not auto rollback
    }
};
