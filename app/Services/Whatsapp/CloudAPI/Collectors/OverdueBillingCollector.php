<?php

namespace App\Services\Whatsapp\CloudAPI\Collectors;

use App\Models\Billings\Billing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class OverdueBillingCollector
{
    /**
     * Mengumpulkan data billing overdue berdasarkan 3 tipe template anti-duplikasi.
     */
    public function collect(Carbon $today, string $slug1, string $slug2, string $slug3): Collection
    {
        $slugs = [$slug1, $slug2, $slug3];

        $query = Billing::query()
            ->select([
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.user_id as user_id', // 💡 Diperlukan oleh AdminBankCollector
                'p.name as package_name',

                // 💡 Menggabungkan nominal jika customer memiliki lebih dari 1 tagihan tertunggak
                DB::raw('SUM(billings.total_amount) as total'),

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

            // --- filter anti-double send hari ini ---
            ->leftJoin('message_events as me', function ($join) use ($today, $slugs) {
                $join->on('billings.customer_id', '=', 'me.customer_id')
                    ->whereIn('me.message_type', $slugs) // 💡 Mendukung pencocokan multi-template
                    ->whereDate('me.created_at', $today)
                    ->whereIn('me.status', ['success', 'failed', 'pending']);
            })

            ->whereNull('me.id')

            // --- rule bisnis overdue (sesuai kondisi logikamu) ---
            ->where(function ($q) use ($today) {
                // BL
                $q->where(function ($q) use ($today) {
                    $q->where('billings.status', 'BL')
                        ->whereDate('billings.due_date', '<', $today); // 💡 Koreksi: Overdue harusnya '<' bukan '>='
                })
                    // PL
                    ->orWhere(function ($q) use ($today) {
                        $q->where('billings.status', 'PL')
                            ->whereDate('billings.paylater_until', '<', $today);
                    });
            })

            // 🔥 Leburkan data billing ID agar me-return tepat 1 baris per customer
            ->groupBy([
                'customers.id',
                'customers.name',
                'customers.contact',
                'customers.user_id',
                'p.name'
            ]);

        // 💡 Ambil semua data tanpa limitasi untuk di-handle delay-nya di level Queue Job
        $data = $query->get();

        LogHelper::logInfo(
            null,
            [
                'date'   => $today->toDateString(),
                'slugs'  => $slugs,
                'count'  => $data->count(),
            ],
            120,
            '[OverdueBillingCollector] Result collected successfully'
        );

        return $data;
    }
}
