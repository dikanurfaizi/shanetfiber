<?php

namespace App\Services\Telegrams\Strategies\Concerns;

use App\Models\Customer;
use App\Models\Billing\Billing;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait LoadsModels
{
    protected function getCustomerOrFail(int $customerId): Customer
    {
        return Customer::with([
            'package',
            'member',
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
            'customer',
            'customer.package',
            'customer.member'
        ])->findOrFail($billingId);
    }

    protected function getReceipt(?int $receiptId): ?Receipt
    {
        return $receiptId
            ? Receipt::with(['billing', 'billing.customer'])->findOrFail($receiptId)
            : null;
    }
}
