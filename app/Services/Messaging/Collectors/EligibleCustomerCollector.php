<?php

namespace App\Services\Messaging\Collectors;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EligibleCustomerCollector
{
    /**
     * Mengumpulkan data customer yang layak menerima broadcast pesan tertentu.
     */
    public function collect(Carbon $today, string $messageType, ?int $customerId = null, bool $isTestMode = false, string $traceId): Collection
    {
        $timer = new ExecutionTimer();
        $query = Customer::query()
            ->select([
                'customers.id as customer_id',
                'customers.customer_id as customer_unique_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.address as customer_address',
                'customers.user_id as user_id',
                'u.name as admin_name',
                'u.contact as admin_contact',
                DB::raw('MAX(p.name) as package_name'),
            ])
            ->leftJoin('users as u', 'customers.user_id', '=', 'u.id')
            ->leftJoin('customer_subscriptions as cs', fn($j) => $j->on('customers.id', '=', 'cs.customer_id')->where('cs.status', 'active'))
            ->leftJoin('packages as p', 'cs.package_id', '=', 'p.id')
            ->join('billings as b', 'customers.id', '=', 'b.customer_id')

            // 💡 REUSABLE SCOPES (Dari Model Customer)
            ->eligibleForBroadcast()
            ->whereValidJid($messageType)

            ->where('customers.user_id', '!=', 1)
            ->whereIn('b.status', ['BL', 'PL'])
            ->whereMonth('b.due_date', $today->month)
            ->whereYear('b.due_date', $today->year);

        // Single Resend vs Mass Broadcast Anti-Double
        if (!is_null($customerId)) {
            $query->where('customers.id', $customerId);
        } else {
            $query->whereUnsentMessageType($messageType);
        }

        $query->groupBy([
            'customers.id',
            'customers.customer_id',
            'customers.name',
            'customers.contact',
            'customers.address',
            'customers.user_id',
            'u.name',
            'u.contact',
        ]);

        // Throttling / Rate Limiting untuk Mass Broadcast
        if (!$isTestMode && is_null($customerId)) {
            $query->limit(15);
        }

        $data = $query->get();

        // Sample Mode untuk Testing
        if ($isTestMode && is_null($customerId) && $data->isNotEmpty()) {
            $data = $data->groupBy('user_id')->map(fn($group) => $group->first())->values();
        }

        LogHelper::logInfo(
            null,
            [
                'date'        => $today->toDateString(),
                'count'       => $data->count(),
                'test_mode'   => $isTestMode ? 'ON' : 'OFF',
                'customer_id' => $customerId ?? 'ALL',
            ],
            200,
            '[BroadcastCustomerCollector] Collected eligible customers successfully',
            $traceId,
            $timer->lap()
        );

        return $data;
    }
}
