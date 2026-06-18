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
     * * @param Carbon $today Tanggal hari ini 
     * @param string $messageType Slug utama pengiriman (e.g., 'payment_channel_update')
     * @param int|null $customerId ID Customer spesifik untuk kebutuhan Single Resend (Opsional)
     */
    public function collect(Carbon $today, string $messageType, ?int $customerId = null): Collection
    {
        $testMode = config('messaging.reminder_allow_test', false);

        // ===============================================================
        // 1. Ambil Semua ID Customer yang INVALID_JID Terlebih Dahulu (Early Exclude)
        // ===============================================================
        $invalidJidCustomerIds = DB::table('message_events')
            ->where('message_type', '=', $messageType)
            ->where('status', '=', 'failed')
            ->where('response_code', '=', 'INVALID_JID')
            ->whereNotNull('customer_id')
            ->distinct()
            ->pluck('customer_id')
            ->toArray();

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

            // --- FILTER STATUS BILLING & BULAN INI ---
            ->whereIn('b.status', ['BL', 'PL'])
            // 💡 Menambahkan filter bulan ini berdasarkan parameter $today
            // Ganti 'b.due_date' dengan nama kolom tanggal billing Anda (misal: b.created_at atau b.billing_date)
            ->whereMonth('b.due_date', $today->month)
            ->whereYear('b.due_date', $today->year);

        // ===============================================================
        // 3. TERAPKAN PENGECUALIAN (EXCLUDE INVALID JID)
        // ===============================================================
        if (!empty($invalidJidCustomerIds)) {
            $query->whereNotIn('customers.id', $invalidJidCustomerIds);
        }

        // ===============================================================
        // 4. CONDITIONAL FILTER: ANTI-DOUBLE ATAU SINGLE RESEND
        // ===============================================================
        if (!is_null($customerId)) {
            $query->where('customers.id', '=', $customerId);
        } else {
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
                'total_invalid_wapi' => count($invalidJidCustomerIds)
            ],
            120,
            '[ChannelUpdateCustomerCollector] Dynamic customer data collected successfully with clear early-exclude'
        );

        return $data;
    }
}
