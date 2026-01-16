<?php

namespace App\Services\Customers\Billing;

use App\Models\Billings\Billing;
use App\Models\Billings\BillingItem;
use App\Models\Customers\CustomerSubscription;
use App\Services\Billings\BillingCreateService;
use App\Models\Websystem;

class BillingRecalculatorService
{
    public function recalcPackage(
        Billing $billing,
        CustomerSubscription $subscription,
        Websystem $websystem,
        BillingCreateService $billingCreateService
    ): void {

        BillingItem::where('billing_id', $billing->id)
            ->where('item_type', 'package')
            ->delete();

        $price = $subscription->package->price;

        BillingItem::create([
            'billing_id' => $billing->id,
            'item_type'  => 'package',
            'source_type' => 'package',
            'source_id'  => $subscription->package_id,
            'item_name'  => $subscription->package->name,
            'qty'        => 1,
            'price'      => $price,
            'subtotal'   => $price,
        ]);

        $subtotal = BillingItem::where('billing_id', $billing->id)->sum('subtotal');

        $tax = $billingCreateService->calculateTax($websystem, $subtotal);

        $billing->update([
            'subtotal'     => $subtotal,
            'tax_amount'   => $tax,
            'total_amount' => $subtotal + $tax,
        ]);
    }
}
