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

        // ===============================================================
        // 1. Ambil Semua ID Customer yang INVALID_JID Terlebih Dahulu (Early Exclude)
        // ===============================================================
        // Tarik kumpulan ID customer dari tabel message_events yang gagal karena nomor tidak valid
        $invalidJidCustomerIds = DB::table('message_events')
            ->where('message_type', '=', $messageType)
            ->where('status', '=', 'failed')
            ->where('response_code', '=', 'INVALID_JID')
            ->whereNotNull('customer_id')
            ->distinct()
            ->pluck('customer_id')
            ->toArray(); // Diubah menjadi bentuk array murni murni [1, 5, 12, dst]

        // ===============================================================
        // 2. QUERY UTAMA DATA CUSTOMER
        // ===============================================================
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
            ->join('billings as b', 'customers.id', '=', 'b.customer_id')

            ->where('customers.user_id', '!=', 1)
            ->where('customers.disabled', '=', 'false')
            ->where('customers.activation', '=', 'true')
            ->where('cs.status', '=', 'active')

            // --- FILTER STATUS BILLING ---
            ->whereIn('b.status', ['BL', 'PL']);

        // ===============================================================
        // 3. TERAPKAN PENGECUALIAN (EXCLUDE INVALID JID)
        // ===============================================================
        if (!empty($invalidJidCustomerIds)) {
            // Langsung buang semua customer ID yang nomor WA-nya rusak/salah
            $query->whereNotIn('customers.id', $invalidJidCustomerIds);
        }

        // ===============================================================
        // 4. CONDITIONAL FILTER: ANTI-DOUBLE ATAU SINGLE RESEND
        // ===============================================================
        if (!is_null($customerId)) {
            $query->where('customers.id', '=', $customerId);
        } else {
            // Sisa pengecekan di dalam whereDoesntHave sekarang menjadi sangat tipis dan ringan 
            // Karena hanya perlu menjaga status success dan pending saja
            $query->whereDoesntHave('messageEvents', function ($sub) use ($messageType) {
                $sub->where('message_type', '=', $messageType)
                    ->whereIn('status', ['success', 'pending', 'failed']);
            });
        }

        // Group by menjaga agar customer tetap unik (1 customer saja)
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
                'date'               => $today->toDateString(),
                'count'              => $data->count(),
                'test_mode'          => $testMode ? 'ON' : 'OFF',
                'customer_id'        => $customerId ?? 'ALL',
                'total_invalid_wapi' => count($invalidJidCustomerIds) // Bagus untuk metrik analisis logs
            ],
            120,
            '[ChannelUpdateCustomerCollector] Dynamic customer data collected successfully with clear early-exclude'
        );

        return $data;
    }
}
