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
     * Mengumpulkan semua data customer yang belum pernah menerima pesan broadcast tipe ini.
     * 
     * @param Carbon $today Tanggal hari ini (tetap dipertahankan untuk log)
     * @param string $messageType Slug utama pengiriman (e.g., 'info_perubahan_rekening_pembayaran')
     * @param string ...$slugs Berapapun jumlah template slug yang ingin dicek (Dinamis)
     */
    public function collect(Carbon $today, string $messageType, string ...$slugs): Collection
    {
        $testMode = config('messaging.reminder_allow_test', false);

        // Gabungkan semua slug (termasuk messageType) ke dalam satu array untuk keperluan logging
        $allSlugs = array_merge([$messageType], $slugs);

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
            // Menggunakan array $allSlugs secara dinamis di dalam query
            ->leftJoin('message_events as me', function ($join) use ($messageType, $slugs) {
                $join->on('customers.id', '=', 'me.customer_id')
                    ->whereIn('me.status', ['success', 'failed', 'pending'])
                    ->where(function ($q) use ($messageType, $slugs) {
                        // Cek apakah customer sudah pernah menerima slug utama
                        $q->where('me.message_type', '=', $messageType);

                        // Jika ada tambahan template slugs lain, masukkan ke dalam orWhereIn
                        if (!empty($slugs)) {
                            $q->orWhereIn('me.message_type', $slugs);
                        }
                    });
            })

            // --- JOIN DAN FILTER BILLING STATUS ---
            // Menggunakan join biasa (Inner Join) karena status wajib berstatus BL atau PL
            ->join('billings as b', 'customers.id', '=', 'b.customer_id')

            // --- CONDITIONS / WHERE CLAUSES ---
            ->whereNull('me.id')
            ->where('customers.user_id', '!=', 1)
            ->whereIn('b.status', ['BL', 'PL']);

        $query->groupBy([
            'customers.id',
            'customers.name',
            'customers.contact',
            'customers.user_id'
        ]);

        // Mengambil semua data tanpa limitasi kuota penarikan data
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
            'ActiveCustomerCollector', // Mengisi channel name log agar tidak null
            [
                'date'      => $today->toDateString(),
                'count'     => $data->count(),
                'test_mode' => $testMode ? 'ON' : 'OFF',
                'slugs'     => $allSlugs // Log semua slug yang dilewati secara dinamis
            ],
            120,
            '[ActiveCustomerCollector] Dynamic customer data collected successfully'
        );

        return $data;
    }
}
