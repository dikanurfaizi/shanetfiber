<?php

namespace App\Services\Whatsapp\CloudAPI\Collectors;

use App\Models\Billings\Billing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class BillingReminderCollector
{
    /**
     * Mengumpulkan data billing berdasarkan 3 slug template anti-duplikasi hari ini (Maksimal 10 data).
     */
    public function collect(Carbon $today, string $slug1, string $slug2, string $slug3): Collection
    {
        $slugs = [$slug1, $slug2, $slug3];

        $query = Billing::query()
            ->select([
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.user_id as user_id', // 💡 Wajib di-select untuk kebutuhan AdminBankCollector
                'p.name as package_name',

                // 💡 Menggunakan SUM agar jika ada double tagihan (PL & BL), totalnya otomatis digabung
                DB::raw('SUM(billings.total_amount) as total'),

                // 💡 Menggunakan MAX untuk mengambil tanggal terjauh/terakhir jika ada dua tagihan
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

            // --- FILTER SAKTI: Cek apakah sudah pernah dikirim salah satu dari 3 template hari ini ---
            ->leftJoin('message_events as me', function ($join) use ($today, $slugs) {
                $join->on('billings.customer_id', '=', 'me.customer_id')
                    ->whereIn('me.message_type', $slugs) // 💡 Menggunakan whereIn untuk 3 jenis template sekaligus
                    ->whereDate('me.created_at', $today)
                    ->whereIn('me.status', ['success', 'failed', 'pending']);
            })

            ->whereNull('me.id')

            // --- rule bisnis reminder (BL <= 15 ATAU PL <= 15) ---
            ->where(function ($q) use ($today) {
                // Kasus BL (Belum Lunas)
                $q->where(function ($q) use ($today) {
                    $q->where('billings.status', 'BL')
                        ->whereMonth('billings.due_date', $today->month)
                        ->whereYear('billings.due_date', $today->year)
                        ->whereDay('billings.due_date', '<=', 15);
                })
                    // Kasus PL (Paylater)
                    ->orWhere(function ($q) use ($today) {
                        $q->where('billings.status', 'PL')
                            ->whereMonth('billings.paylater_until', $today->month)
                            ->whereYear('billings.paylater_until', $today->year)
                            ->whereDay('billings.paylater_until', '<=', 15);
                    });
            })

            // 🔥 GROUP BY DIKUNCI PER CUSTOMER & PAKET
            // Menghapus 'billings.id' dari group by agar data PL & BL milik customer yang sama melebur jadi 1 baris
            ->groupBy([
                'customers.id',
                'customers.name',
                'customers.contact',
                'customers.user_id',
                'p.name'
            ]);

        // 💡 Di-limit maksimal 10 data per eksekusi untuk menghindari throttling/pembatasan billing dari Meta
        $data = $query->limit(10)->get();

        // --- logging status ---
        LogHelper::logInfo(
            null,
            [
                'date'   => $today->toDateString(),
                'slugs'  => $slugs,
                'count'  => $data->count(),
            ],
            120,
            '[BillingReminderCollector] Result collected successfully with throttle limit'
        );

        return $data;
    }
}
