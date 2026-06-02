<?php

namespace App\Services\Whatsapp\CloudAPI\Collectors;

use App\Models\Customers\Customer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class ActiveCustomerCollector
{
    /**
     * Mengumpulkan data customer yang belum pernah menerima pesan broadcast tipe ini.
     * * @param Carbon $today Tanggal hari ini (tetap dipertahankan untuk log)
     * @param string $messageType Slug utama pengiriman (e.g., 'info_perubahan_rekening_pembayaran')
     * @param string $slug1 Tambahan pengecekan slug pembanding 1
     * @param string $slug2 Tambahan pengecekan slug pembanding 2
     * @param string $slug3 Tambahan pengecekan slug pembanding 3
     */
    public function collect(Carbon $today, string $messageType, string $slug1, string $slug2, string $slug3): Collection
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

            // Kunci status subscription hanya yang ACTIVE
            ->leftJoin('customer_subscriptions as cs', function ($join) {
                $join->on('customers.id', '=', 'cs.customer_id')
                    ->where('cs.status', '=', 'active');
            })
            ->leftJoin('packages as p', 'cs.package_id', '=', 'p.id')

            // --- FILTER ANTI-DOUBLE (MESSAGE EVENTS) ---
            // Pengecekan murni berdasarkan message_type & tambahan slugs tanpa filter tanggal me.created_at
            ->leftJoin('message_events as me', function ($join) use ($messageType, $slug1, $slug2, $slug3) {
                $join->on('customers.id', '=', 'me.customer_id')
                    ->whereIn('me.status', ['success', 'failed', 'pending'])
                    ->where(function ($q) use ($messageType, $slug1, $slug2, $slug3) {
                        // Cek apakah customer sudah pernah menerima slug utama ATAU slugs tambahan lainnya
                        $q->where('me.message_type', '=', $messageType)
                            ->orWhereIn('me.message_type', [$slug1, $slug2, $slug3]);
                    });
            })
            ->whereNull('me.id')

            // --- FILTER USER ID BUKAN ADMIN UTAMA (Bukan ID 1) ---
            ->where('customers.user_id', '!=', 1);

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
            $query->limit(10); // Tetap di-limit 10 data per eksekusi
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
            [
                'date'      => $today->toDateString(),
                'count'     => $data->count(),
                'test_mode' => $testMode ? 'ON' : 'OFF',
                'slugs'     => [$messageType, $slug1, $slug2, $slug3]
            ],
            120,
            '[ActiveCustomerCollector] Dynamic customer data collected successfully'
        );

        return $data;
    }
}
