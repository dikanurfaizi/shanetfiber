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
     * @param int|null $customerId ID Customer spesifik untuk kebutuhan Single Resend (Opsional)
     */
    public function collect(Carbon $today, string $messageType, ?int $customerId = null): Collection
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

            // --- JOIN BILLINGS ---
            // Gunakan INNER JOIN jika broadcast ini WAJIB hanya untuk yang punya tagihan bermasalah
            ->join('billings as b', 'customers.id', '=', 'b.customer_id')

            ->where('customers.user_id', '!=', 1)
            ->where('customers.disabled', '=', 'false')
            ->where('customers.activation', '=', 'true')
            ->where('cs.status', '=', 'active')

            // --- FILTER STATUS BILLING ---
            ->whereIn('b.status', ['BL', 'PL']);

        // ===============================================================
        // CONDITIONAL FILTER: ANTI-DOUBLE ATAU SINGLE RESEND
        // ===============================================================
        if (!is_null($customerId)) {
            $query->where('customers.id', '=', $customerId);
        } else {
            // Memastikan sub-query mengikat field tabel message_events secara eksplisit
            $query->whereDoesntHave('messageEvents', function ($sub) use ($messageType) {
                $sub->where('message_type', '=', $messageType)
                    ->where(function ($q) {
                        // Gunakan nama tabel default laravel untuk relasi (biasanya message_events)
                        $q->where('status', '=', 'success')
                            ->orWhere('status', '=', 'pending')
                            ->orWhere(function ($failQuery) {
                                $failQuery->where('status', '=', 'failed')
                                    ->where('response_code', '=', 'INVALID_JID');
                            });
                    });
            });
        }

        // group by menjaga agar customer tetap unik (1 customer saja) meskipun record billing/message_events double di DB
        $query->groupBy([
            'customers.id',
            'customers.name',
            'customers.contact',
            'customers.user_id'
        ]);

        // ===============================================================
        // RATE LIMITER ENGINE (THROTTLING) - Hanya jalan jika Broadcast Massal
        // ===============================================================
        if (!$testMode && is_null($customerId)) {
            $query->limit(5);
        }

        $data = $query->get();

        // ===============================================================
        // TEST MODE ENGINE - Hanya jalan jika Broadcast Massal
        // ===============================================================
        if ($testMode && is_null($customerId) && $data->isNotEmpty()) {
            $data = $data->groupBy('user_id')->map(function ($group) {
                return $group->first();
            })->values();
        }

        LogHelper::logInfo(
            null,
            [
                'date'        => $today->toDateString(),
                'count'       => $data->count(),
                'test_mode'   => $testMode ? 'ON' : 'OFF',
                'customer_id' => $customerId ?? 'ALL'
            ],
            120,
            '[ChannelUpdateCustomerCollector] Dynamic customer data collected successfully'
        );

        return $data;
    }
}
