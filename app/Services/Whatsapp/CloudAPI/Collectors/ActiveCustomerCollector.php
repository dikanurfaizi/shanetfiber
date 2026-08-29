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

        // Gabungkan semua slug untuk filter anti-double send
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

            ->whereNotExists(function ($q) use ($allSlugs) {
                $q->select(DB::raw(1))
                    ->from('message_events as me')
                    ->whereColumn('me.customer_id', 'customers.id')
                    ->whereIn('me.message_type', $allSlugs)
                    ->whereIn('me.status', ['success', 'failed', 'pending']);
            })

            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('billings as b')
                    ->whereColumn('b.customer_id', 'customers.id')
                    ->whereIn('b.status', ['BL', 'PL']);
            })

            // --- STANDAR KONDISI BISNIS ---
            ->where('customers.user_id', '!=', 1)
            ->whereNotNull('customers.contact')
            ->where('customers.is_opt_out', 0)
            ->groupBy([
                'customers.id',
                'customers.name',
                'customers.contact',
                'customers.user_id'
            ]);

        // Mengambil semua data secara instan
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
            'ActiveCustomerCollector',
            [
                'date'      => $today->toDateString(),
                'count'     => $data->count(),
                'test_mode' => $testMode ? 'ON' : 'OFF',
                'slugs'     => $allSlugs
            ],
            120,
            '[ActiveCustomerCollector] Dynamic active customer data collected with optimized existential constraints'
        );

        return $data;
    }
}
