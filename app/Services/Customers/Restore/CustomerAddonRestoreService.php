<?php

namespace App\Services\Customers\Restore;

use App\Models\Customers\Customer;
use App\Models\Packages\Addon as PackagesAddon;

class CustomerAddonRestoreService
{
    public function handle(Customer $customer, array $addonsPayload): void
    {
        // Nonaktifkan semua addon lama
        $customer->addons()->update(['is_active' => 0]);

        if (empty($addonsPayload)) {
            return;
        }

        foreach ($addonsPayload as $addonData) {

            if (empty($addonData['addon_id'])) {
                continue;
            }

            $addon = PackagesAddon::where('id', $addonData['addon_id'])
                ->where('is_active', true)
                ->first();

            if (!$addon) {
                continue; // addon deprecated
            }

            $qty = max(1, (int) ($addonData['qty'] ?? 1));
            $unitPrice = (int) ($addonData['unit_price'] ?? $addon->price);

            $customer->addons()->updateOrCreate(
                ['addon_id' => $addon->id],
                [
                    'qty'        => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal'   => $qty * $unitPrice,
                    'is_active'  => 1,
                    'start_date' => now(),
                    'end_date'   => now()->endOfMonth(),
                ]
            );
        }
    }
}
