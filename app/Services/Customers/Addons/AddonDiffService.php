<?php

namespace App\Services\Customers\Addons;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerAddon;

class AddonDiffService
{
    public function diff(Customer $customer, array $incomingMap): array
    {
        $existing = CustomerAddon::where('customer_id', $customer->id)
            ->where('is_active', 1)
            ->get()
            ->keyBy(fn($a) => (int)$a->addon_id);

        $existingMap = $existing->mapWithKeys(fn($a) => [
            (int)$a->addon_id => [
                'qty'        => (int)$a->qty,
                'unit_price' => (float)$a->unit_price,
            ]
        ])->toArray();

        $incomingIds = collect(array_keys($incomingMap));
        $existingIds = collect(array_keys($existingMap));

        $addedIds   = $incomingIds->diff($existingIds)->values()->all();
        $removedIds = $existingIds->diff($incomingIds)->values()->all();

        $changedIds = $incomingIds->intersect($existingIds)
            ->filter(function ($id) use ($incomingMap, $existingMap) {
                return (int)$incomingMap[$id]['qty'] !== (int)$existingMap[$id]['qty']
                    || (float)$incomingMap[$id]['unit_price'] !== (float)$existingMap[$id]['unit_price'];
            })
            ->values()
            ->all();

        $hasChange = !empty($addedIds) || !empty($removedIds) || !empty($changedIds);

        return [
            'has_change' => $hasChange,
            'added'      => $addedIds,
            'removed'    => $removedIds,
            'changed'    => $changedIds,
        ];
    }

    /* ==========================================================
     * Addon normalization + diff (added/removed/changed)
     * ========================================================== */
    public function normalizeAddons(array $addons): array
    {
        // map: addon_id => ['qty' => int, 'unit_price' => float]
        return collect($addons)
            ->filter(fn($a) => !empty($a['addon_id']))
            ->mapWithKeys(fn($a) => [
                (int)$a['addon_id'] => [
                    'qty'        => (int)($a['qty'] ?? 0),
                    'unit_price' => (float)($a['unit_price'] ?? 0),
                ]
            ])->toArray();
    }
}
