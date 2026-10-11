<?php

namespace App\Services\Messaging\Collectors;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use Illuminate\Support\Collection;

class BillingCollector
{
    /**
     * Mengambil daftar paket tagihan aktif milik customer tertentu.
     */
    public function getBillingPackages(int $customerId, string $traceId): Collection
    {
        $timer = new ExecutionTimer();

        try {
            $packages = Billing::query()
                ->select([
                    'packages.name as package_name',
                    'bsp.billing_periode_id',
                    'packages.price',
                    'billings.base_price as package_price',
                    'billings.discount_amount as discount',
                    'billings.status',
                    'billings.due_date',
                    'billings.pay_later',
                    'bsp.billing_periode as periode_name',
                    'billings.total_amount',
                ])
                ->leftJoin('customers', 'billings.customer_id', '=', 'customers.id')
                ->leftJoin('packages', 'customers.package_id', '=', 'packages.id')
                ->leftJoin('billing_subscription_periodes as bsp', 'billings.billing_subscription_id', '=', 'bsp.id')
                ->where('billings.customer_id', $customerId)
                ->whereIn('billings.status', ['BL', 'PL'])
                ->orderByRaw("CASE WHEN billings.status = 'BL' THEN billings.due_date ELSE billings.pay_later END DESC")
                ->get();

            if ($packages->isEmpty()) {
                LogHelper::logWarning(
                    null,
                    ['customer_id' => $customerId],
                    404,
                    "[BillingCollector] No active unpaid billings found for customer_id={$customerId}",
                    $traceId,
                    $timer->lap()
                );
            } else {
                LogHelper::logInfo(
                    null,
                    [
                        'customer_id'   => $customerId,
                        'package_count' => $packages->count(),
                        'total_amount'  => $packages->sum('total_amount'),
                    ],
                    200,
                    '[BillingCollector] Billing packages collected successfully',
                    $traceId,
                    $timer->lap()
                );
            }

            return $packages;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                ['customer_id' => $customerId, 'error' => $e->getMessage(), 'e' => $e],
                is_int($e->getCode()) && $e->getCode() > 0 ? $e->getCode() : 500,
                "[BillingCollector] Error retrieving billing packages for customer_id={$customerId}",
                $traceId,
                $timer->lap()
            );

            return collect();
        }
    }
}
