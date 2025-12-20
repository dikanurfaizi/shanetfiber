<?php

namespace App\Services\Billings;

use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Customers\Customer;
use Illuminate\Support\Collection;

class BillingItemService
{
    public function getLatestBillingItems(Customer $customer): array
    {
        // 1. Ambil billing terakhir berdasarkan PERIODE (bukan created_at)
        $latestBilling = Billing::where('customer_id', $customer->id)
            ->whereHas('billingItems')
            ->join(
                'billing_subscription_periodes',
                'billings.billing_subscription_id',
                '=',
                'billing_subscription_periodes.id'
            )
            ->orderByDesc('billing_subscription_periodes.billing_periode_id')
            ->orderByDesc('billings.id')
            ->select('billings.*')
            ->with('billingItems') // 🔥 eager load
            ->first();

        if (!$latestBilling) {
            return [
                'billing'       => null,
                'packageItems'  => collect(),
                'addonItems'    => collect(),
                'discountItems' => collect(),
            ];
        }

        // 2. Ambil items dari relasi
        $items = $latestBilling->billingItems
            ->sortBy('id');


        LogHelper::logInfo(null, [
            'items' => $items,
            'packageItems'  => $items->where('item_type', 'package')->values(),
            'addonItems'    => $items->where('item_type', 'addon')->values(),
            'discountItems' => $items->where('item_type', 'discount')->values(),
        ], 200, "Detail Billing Items", null);

        // 3. Kelompokkan
        return [
            'billing'       => $latestBilling,
            'packageItems'  => $items->where('item_type', 'package')->values(),
            'addonItems'    => $items->where('item_type', 'addon')->values(),
            'discountItems' => $items->where('item_type', 'discount')->values(),
        ];
    }
}
