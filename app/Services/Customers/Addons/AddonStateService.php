<?php

namespace App\Services\Customers\Addons;

use App\Helpers\LogHelper;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerAddon;
use Illuminate\Support\Str;

class AddonStateService
{
    /* ==========================================================
     * Apply addon state to customer_addons (add/update/deactivate)
     * ========================================================== */
    public function apply(Customer $customer, array $incomingMap, array $diff, BillingPeriode $billingPeriode, string $traceId): void
    {
        $existing = CustomerAddon::where('customer_id', $customer->id)
            ->where('is_active', 1)
            ->get()
            ->keyBy(fn($a) => (int)$a->addon_id);

        LogHelper::logInfo(null, [
            'customer_id' => $customer->id,
            'incoming_map' => $incomingMap,
            'diff' => $diff,
            'billing_periode_id' => $billingPeriode->id,
        ], 200, 'Applying addon state changes to customer_addons.', $traceId);

        // Removed => deactivate
        foreach ($diff['removed'] as $addonId) {
            if (!isset($existing[$addonId])) continue;

            $existing[$addonId]->update([
                'is_active' => 0,
                'end_date'  => $billingPeriode->end_date,
            ]);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'addon_id'    => $addonId,
            ], 200, 'Deactivated customer_addon due to removal.', $traceId);
        }

        // Changed => update qty/price/subtotal
        foreach ($diff['changed'] as $addonId) {
            if (!isset($existing[$addonId])) continue;

            $qty  = (int) $incomingMap[$addonId]['qty'];
            $price = (float) $incomingMap[$addonId]['unit_price'];

            $existing[$addonId]->update([
                'qty'        => $qty,
                'unit_price' => $price,
                'subtotal'   => $qty * $price,
                'start_date'  => $billingPeriode->start_date,
                'end_date'   => null,
                'is_active'  => 1,
            ]);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'addon_id' => $addonId
            ], 200, 'Change triggered update on customer_addon.', $traceId);
        }

        // Added => create
        foreach ($diff['added'] as $addonId) {
            $qty  = (int) $incomingMap[$addonId]['qty'];
            $price = (float) $incomingMap[$addonId]['unit_price'];

            CustomerAddon::create([
                'slug'        => Str::uuid(),
                'customer_id' => $customer->id,
                'addon_id'    => $addonId,
                'qty'         => $qty,
                'unit_price'  => $price,
                'subtotal'    => $qty * $price,
                'start_date'  => now(),
                'end_date'    => null,
                'is_active'   => 1,
            ]);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'addon_id'    => $addonId,
            ], 200, 'Created new customer_addon due to addition.', $traceId);
        }
    }
}
