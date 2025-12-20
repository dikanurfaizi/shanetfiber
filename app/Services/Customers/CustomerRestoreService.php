<?php

namespace App\Services\Customers;

use Illuminate\Support\Facades\DB;
use App\Models\Customers\Customer;
use App\Helpers\LogHelper;

class CustomerRestoreService
{
    public function restoreCustomer(string $slug, string $traceId): void
    {
        DB::transaction(function () use ($slug, $traceId) {

            // Ambil customer yang dihapus
            $customer = Customer::onlyTrashed()
                ->where('slug', $slug)
                ->lockForUpdate()
                ->firstOrFail();

            // Restore customer
            $customer->restore();

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                ],
                200,
                "Customer restored successfully (restore only, no billing created).",
                $traceId
            );
        });
    }
}
