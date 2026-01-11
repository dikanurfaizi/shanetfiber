<?php

namespace App\Services\Telegrams\Strategies\Concerns;

use App\Models\Customers\Customer;
use App\Models\Billings\Billing;
use App\Models\Receipt;

trait LoadsModels
{
    protected function getCustomerOrFail(int $customerId): Customer
    {
        return Customer::with([
            'user'
        ])->findOrFail($customerId);
    }

    protected function getBilling(?int $billingId): ?Billing
    {
        if (!$billingId) {
            return null;
        }

        return Billing::with([
            'billingSubscription',
            'billingSubscription.billingPeriode',
            'customer'
        ])->findOrFail($billingId);
    }

    protected function getReceipt(?int $receiptId): ?Receipt
    {
        return $receiptId
            ? Receipt::with(['billing', 'billing.customer'])->findOrFail($receiptId)
            : null;
    }
}
