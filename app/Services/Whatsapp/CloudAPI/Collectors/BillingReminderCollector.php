<?php

namespace App\Services\Whatsapp\CloudAPI\Collectors;

use App\Helpers\ExecutionTimer;
use App\Models\Billings\Billing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class BillingReminderCollector
{
    /**
     * Mengumpulkan data billing berdasarkan 3 slug template anti-duplikasi hari ini (Tanpa Limit).
     */
    public function collect(Carbon $today, string $slug1, string $slug2, string $slug3): Collection
    {
        $timer = new ExecutionTimer();
        $slugs = [$slug1, $slug2, $slug3];
        $todayStr = $today->toDateString();

        // Mengoptimalkan filter tanggal (<= 15) menjadi format range ISO Y-m-d agar index database terpakai
        $startRange = $today->copy()->startOfMonth()->toDateString(); // Tanggal 1 bulan ini
        $endRange   = $today->copy()->day(15)->toDateString();       // Tanggal 15 bulan ini

        $query = Billing::query()
            ->select([
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.user_id as user_id',
                'p.name as package_name',

                // Menggunakan SUM agar jika ada double tagihan (PL & BL), totalnya otomatis digabung
                DB::raw('SUM(billings.total_amount) as total'),

                // Menggunakan MAX untuk mengambil tanggal terjauh/terakhir jika ada dua tagihan
                DB::raw('MAX(billings.due_date) as due_date'),
                DB::raw('MAX(billings.paylater_until) as paylater_until'),
                DB::raw('MAX(COALESCE(billings.paylater_until, billings.due_date)) as final_due_date'),
            ])
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.user_id', '=', 'users.id')

            // Kunci status subscription hanya yang ACTIVE
            ->leftJoin('customer_subscriptions as cs', function ($join) {
                $join->on('customers.id', '=', 'cs.customer_id')
                    ->where('cs.status', '=', 'active');
            })
            ->leftJoin('packages as p', 'cs.package_id', '=', 'p.id')

            // 👉 SOLUSI TOTAL ANTI-DUPLIKASI: Menggunakan WHERE NOT EXISTS
            // Menjamin data yang sudah sukses, failed, atau pending hari ini dengan tipe slug tersebut tidak akan lolos query.
            ->whereNotExists(function ($q) use ($slugs, $todayStr) {
                $q->select(DB::raw(1))
                    ->from('message_events as me')
                    ->whereColumn('me.customer_id', 'billings.customer_id')
                    ->whereIn('me.message_type', $slugs)
                    ->whereDate('me.created_at', $todayStr)
                    ->whereIn('me.status', ['success', 'failed', 'pending']);
            })

            // --- OPTIMASI QUERY: Menggunakan WhereBetween menggantikan whereMonth/whereDay agar INDEX pada table Billing tetap aktif ---
            ->where(function ($q) use ($startRange, $endRange) {
                // Kasus BL (Belum Lunas) jatuh tempo antara tanggal 1 - 15 bulan ini
                $q->where(function ($subQ) use ($startRange, $endRange) {
                    $subQ->where('billings.status', 'BL')
                        ->whereBetween('billings.due_date', [$startRange, $endRange]);
                })
                    // Kasus PL (Paylater) jatuh tempo antara tanggal 1 - 15 bulan ini
                    ->orWhere(function ($subQ) use ($startRange, $endRange) {
                        $subQ->where('billings.status', 'PL')
                            ->whereBetween('billings.paylater_until', [$startRange, $endRange]);
                    });
            })

            // GROUP BY DIKUNCI PER CUSTOMER & PAKET
            ->groupBy([
                'customers.id',
                'customers.name',
                'customers.contact',
                'customers.user_id',
                'p.name'
            ]);

        // 💡 Diperbaiki: ->limit(10) dihapus total agar mengambil seluruh data tertunggak
        $data = $query->get();

        // --- logging status ---
        LogHelper::logInfo(
            null,
            [
                'date'         => $todayStr,
                'slugs'        => $slugs,
                'count'        => $data->count(),
                'execution_ms' => $timer->lap()
            ],
            120,
            '[BillingReminderCollector] Result collected successfully without limit and fully optimized query execution'
        );

        return $data;
    }
}
