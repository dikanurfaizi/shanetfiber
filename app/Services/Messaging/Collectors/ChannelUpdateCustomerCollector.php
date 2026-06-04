<?php

namespace App\Services\Messaging\Collectors;

use App\Models\Customers\Customer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class ChannelUpdateCustomerCollector
{
    /**
     * Mengumpulkan data customer yang belum pernah menerima pesan broadcast tipe ini.
     * * @param Carbon $today Tanggal hari ini (tetap dipertahankan untuk log)
     * @param string $messageType Slug utama pengiriman (e.g., 'payment_channel_update')
     */
    public function collect(Carbon $today, string $messageType): Collection
    {
        $testMode = config('messaging.reminder_allow_test', false);

        $query = Customer::query()
            ->select([
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.user_id as user_id',
                DB::raw('MAX(p.name) as package_name'),
            ])
            ->leftJoin('users as u', 'customers.user_id', '=', 'u.id')
            ->leftJoin('customer_subscriptions as cs', function ($join) {
                $join->on('customers.id', '=', 'cs.customer_id')
                    ->where('cs.status', '=', 'active');
            })
            ->leftJoin('packages as p', 'cs.package_id', '=', 'p.id')

            // --- TAMBAHKAN JOIN BILLINGS ---
            ->leftJoin('billings as b', 'customers.id', '=', 'b.customer_id')

            // --- FILTER ANTI-DOUBLE (MESSAGE EVENTS) ---
            ->leftJoin('message_events as me', function ($join) use ($messageType) {
                $join->on('customers.id', '=', 'me.customer_id')
                    ->where('me.message_type', '=', $messageType)
                    ->where(function ($query) {
                        $query->whereIn('me.status', ['success', 'pending'])
                            ->orWhere(function ($q) {
                                $q->where('me.status', '=', 'failed')
                                    ->where('me.response_code', '=', 'INVALID JID');
                            });
                    });
            })
            ->whereNull('me.id')
            ->where('customers.user_id', '!=', 1)
            ->where('customers.disabled', '=', 'false')
            ->where('customers.activation', '=', 'true')
            ->where('cs.status', '=', 'active')

            // --- TAMBAHKAN FILTER STATUS BILLING DISINI ---
            ->whereIn('b.status', ['BL', 'PL']);

        $query->groupBy([
            'customers.id',
            'customers.name',
            'customers.contact',
            'customers.user_id'
        ]);

        // ===============================================================
        // RATE LIMITER ENGINE (THROTTLING)
        // ===============================================================
        if (!$testMode) {
            $query->limit(5);
        }

        $data = $query->get();

        // ===============================================================
        // TEST MODE ENGINE: POTONG DATA (1 CUSTOMER PER ADMIN)
        // ===============================================================
        if ($testMode && $data->isNotEmpty()) {
            $data = $data->groupBy('user_id')->map(function ($group) {
                return $group->first();
            })->values();
        }

        LogHelper::logInfo(
            null,
            ['date' => $today->toDateString(), 'count' => $data->count(), 'test_mode' => $testMode ? 'ON' : 'OFF'],
            120,
            '[ChannelUpdateCustomerCollector] Dynamic customer data collected successfully'
        );

        return $data;
    }
}
