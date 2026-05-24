<?php

namespace App\Services\Whatsapp\CloudAPI\Collectors;

use App\Models\Billings\Billing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class ActiveCustomerCollector
{
    public function collect(Carbon $today, string $slug1, string $slug2, string $slug3): Collection
    {
        $testMode = config('messaging.reminder_allow_test', false);

        $query = Billing::query()
            ->select([
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.user_id as user_id', // Dipetakan kembali ke key user_id relasi admin bank

                // 👉 Menggunakan MAX untuk mengantisipasi jika customer punya > 1 subs aktif/paket berbeda
                DB::raw('MAX(p.name) as package_name'),

                // 👉 Jika ada 2 billing aktif, nominalnya digabung (akumulasi) agar informatif
                DB::raw('SUM(billings.total_amount) as total'),

                // 👉 Ambil batas waktu jatuh tempo paling maksimal/terakhir
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

            // --- filter sudah pernah dikirim hari ini ---
            ->leftJoin('message_events as me', function ($join) use ($today, $slug1, $slug2, $slug3) {
                $join->on('billings.customer_id', '=', 'me.customer_id')
                    ->where(function ($q) use ($slug1, $slug2, $slug3) {
                        $q->whereIn('me.message_type', [$slug1, $slug2, $slug3]);
                    })
                    ->whereDate('me.created_at', $today)
                    ->whereIn('me.status', ['success', 'failed', 'pending']);
            })

            ->whereNull('me.id');

        // ===============================================================
        // CONDITIONAL FILTER BASED ON MODE
        // ===============================================================
        if (!$testMode) {
            // JIKA PRODUCTION: Jalankan business rules penyaringan tanggal yang ketat
            $query->where(function ($q) use ($today) {
                // Status BL (Belum Lunas)
                $q->where(function ($q) use ($today) {
                    $q->where('billings.status', 'BL')
                        ->whereMonth('billings.due_date', $today->month)
                        ->whereYear('billings.due_date', $today->year)
                        ->whereDay('billings.due_date', '<=', 15);
                })
                    // Status PL (Paylater)
                    ->orWhere(function ($q) use ($today) {
                        $q->where('billings.status', 'PL')
                            ->whereMonth('billings.paylater_until', $today->month)
                            ->whereYear('billings.paylater_until', $today->year)
                            ->whereDay('billings.paylater_until', '<=', 15);
                    });
            });
        } else {
            // JIKA TEST MODE: Longgarkan aturan tanggal, ambil semua billing yang masih menunggak (BL/PL)
            $query->whereIn('billings.status', ['BL', 'PL']);
        }

        // Group by mutlak pada entitas customer unik
        $query->groupBy([
            'customers.id',
            'customers.name',
            'customers.contact',
            'customers.user_id'
        ]);

        $data = $query->get();

        // ===============================================================
        // TEST MODE ENGINE: POTONG DATA (1 CUSTOMER PER ADMIN)
        // ===============================================================
        if ($testMode && $data->isNotEmpty()) {
            // Menggunakan fungsi group Collection Laravel berdasarkan user_id (admin), lalu ambil baris pertama saja
            $data = $data->groupBy('user_id')->map(function ($group) {
                return $group->first();
            })->values();
        }

        // --- logging ringan ---
        LogHelper::logInfo(
            null,
            [
                'date'      => $today->toDateString(),
                'slug1'     => $slug1,
                'slug2'     => $slug2,
                'slug3'     => $slug3,
                'count'     => $data->count(),
                'test_mode' => $testMode ? 'ON' : 'OFF'
            ],
            120,
            '[BillingReminderCollector] Result collected'
        );

        return $data;
    }
}
