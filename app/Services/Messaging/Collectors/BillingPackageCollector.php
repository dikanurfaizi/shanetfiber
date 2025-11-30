<?php

namespace App\Services\Messaging\Collectors;

use App\Models\Billing\Billing;
use Illuminate\Support\Collection;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class BillingPackageCollector
{
    public function getBillingPackages(int $customerId): Collection
    {
        try {
            $packages = Billing::query()
                ->leftJoin('customers', 'billings.customer_id', '=', 'customers.id')
                ->leftJoin('packages', 'customers.package_id', '=', 'packages.id')
                ->leftJoin('members', 'customers.member_id', '=', 'members.id')
                ->leftJoin('billing_subscription_periodes as bsp', 'billings.billing_subscription_id', '=', 'bsp.id')
                ->where('billings.customer_id', $customerId)
                ->whereIn('billings.status', ['BL', 'PL'])
                ->select([
                    'packages.name as package_name',
                    'bsp.billing_periode_id',
                    'packages.price',
                    'billings.base_price as package_price',
                    'billings.discount_amount as discount',
                    'members.type',
                    'billings.status',
                    'billings.due_date',
                    'billings.pay_later',
                    'bsp.billing_periode as periode_name',
                    'billings.total_amount',
                ])
                ->orderByRaw("
                    CASE
                        WHEN billings.status = 'BL' THEN billings.due_date
                        ELSE billings.pay_later
                    END DESC
                ")
                ->get();

            if ($packages->isEmpty()) {
                LogHelper::logWarning(null, ['customer_id' => $customerId], 404, "No billing packages found");
            }

            return $packages;
        } catch (\Throwable $e) {
            LogHelper::logError($e, ['customer_id' => $customerId], 500, "Error retrieving billing packages: {$e->getMessage()}");
            return collect();
        }
    }
}
