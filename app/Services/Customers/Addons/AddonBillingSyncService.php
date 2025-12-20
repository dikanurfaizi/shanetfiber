<?php

namespace App\Services\Customers\Addons;

use App\Models\Billings\Billing;
use App\Models\Billings\BillingItem;
use App\Models\Packages\Addon;

class AddonBillingSyncService
{
    /* ==========================================================
     * Sync billing_items for addons + recalc billing totals
     * (tanpa prorate, tanpa ledger versioning)
     * ========================================================== */
    public function sync(
        Billing $billing,
        array $incomingMap,
        array $diff
    ): void {
        // Ambil semua addon yang terlibat (added + changed)
        $affectedAddonIds = array_values(array_unique(
            array_merge($diff['added'], $diff['changed'])
        ));

        // Ambil nama addon dari master (1 query)
        $addonNames = Addon::whereIn('id', $affectedAddonIds)
            ->pluck('name', 'id'); // [id => name]

        /* ============================
         * REMOVED → delete billing item
         * ============================ */
        foreach ($diff['removed'] as $addonId) {
            BillingItem::where('billing_id', $billing->id)
                ->where('item_type', 'addon')
                ->where('source_type', 'addon')
                ->where('source_id', $addonId)
                ->delete();
        }

        /* ============================
         * ADDED + CHANGED → upsert
         * ============================ */
        foreach ($affectedAddonIds as $addonId) {

            // Safety guard
            if (!isset($incomingMap[$addonId])) {
                continue;
            }

            $qty   = (int) $incomingMap[$addonId]['qty'];
            $price = (float) $incomingMap[$addonId]['unit_price'];
            $subtotal = $qty * $price;

            $itemName = $addonNames[$addonId] ?? 'Addon'; // fallback (harusnya jarang kepakai)

            BillingItem::updateOrCreate(
                [
                    'billing_id'  => $billing->id,
                    'item_type'   => 'addon',
                    'source_type' => 'addon',
                    'source_id'   => $addonId,
                ],
                [
                    'item_name' => $itemName,
                    'qty'       => $qty,
                    'price'     => $price,
                    'subtotal'  => $subtotal,
                ]
            );
        }

        /* ============================
         * RECALC BILLING HEADER
         * ============================ */
        $itemsSubtotal = (float) BillingItem::where('billing_id', $billing->id)
            ->sum('subtotal');

        // tax (sesuaikan field yang pasti ada)
        $taxPercent = (float) ($billing->tax_percent ?? 0);
        $taxAmount  = round($itemsSubtotal * ($taxPercent / 100), 2);

        $billing->update([
            'total_amount' => $itemsSubtotal + $taxAmount,
        ]);
    }
}
