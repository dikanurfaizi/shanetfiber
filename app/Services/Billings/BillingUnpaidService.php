<?php

namespace App\Services\Billings;

use App\Models\Billings\Billing;
use App\Helpers\LogHelper;

class BillingUnpaidService
{
    public function hasUnpaidBills(int $customerId, string $traceId): bool
    {
        try {
            $exists = Billing::where('customer_id', $customerId)
                ->whereNotIn('status', ['VD', 'LS'])
                ->exists();

            if ($exists) {
                LogHelper::logInfo(
                    null,
                    ['customer_id' => $customerId],
                    200,
                    'Customer has unpaid bills',
                    $traceId
                );
            }

            return $exists;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customerId
                ],
                500,
                'Failed to check unpaid bills',
                $traceId
            );
            throw $e;
        }
    }
}
