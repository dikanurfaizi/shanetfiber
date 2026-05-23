<?php

namespace App\Services\Whatsapp\CloudAPI\Collectors;

use App\Models\Billings\Billing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class BillingReminderCollector
{
    public function collect(Carbon $today, string $slug): Collection
    {
        $query = Billing::query()
            ->select([
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'p.name as package_name',
                'billings.total_amount as total',
                'billings.due_date',
                'billings.paylater_until',

                // 👉 final due date ditentukan di DB
                DB::raw('COALESCE(billings.paylater_until, billings.due_date) as final_due_date'),
            ])
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.user_id', '=', 'users.id')

            // 🔥 PERBAIKAN DI SINI: Kunci status subscription hanya yang ACTIVE
            ->leftJoin('customer_subscriptions as cs', function ($join) {
                $join->on('customers.id', '=', 'cs.customer_id')
                    ->where('cs.status', '=', 'active'); // Mengabaikan terminated/suspend
            })
            ->leftJoin('packages as p', 'cs.package_id', '=', 'p.id')

            // --- filter sudah pernah dikirim hari ini ---
            ->leftJoin('message_events as me', function ($join) use ($today, $slug) {
                $join->on('billings.customer_id', '=', 'me.customer_id')
                    ->where('me.message_type', $slug)
                    ->whereDate('me.created_at', $today)
                    ->whereIn('me.status', ['success', 'failed', 'pending']);
            })

            ->whereNull('me.id')

            // --- rule bisnis reminder ---
            ->where(function ($q) use ($today) {
                // BL
                $q->where(function ($q) use ($today) {
                    $q->where('billings.status', 'BL')
                        ->whereMonth('billings.due_date', $today->month)
                        ->whereYear('billings.due_date', $today->year)
                        ->whereDay('billings.due_date', '<=', 15);
                })
                    // PL
                    ->orWhere(function ($q) use ($today) {
                        $q->where('billings.status', 'PL')
                            // 💡 Saran: Tambahkan filter Month untuk PL jika tipenya bulanan seperti BL
                            ->whereMonth('billings.paylater_until', $today->month)
                            ->whereYear('billings.paylater_until', $today->year)
                            ->whereDay('billings.paylater_until', '<=', 15);
                    });
            })

            // 🔥 ANTIMISALNYA: Jika seandainya ada case khusus 1 customer punya 2 active subscription,
            // group by ini memastikan data billing keluar tepat 1 baris per customer.
            ->groupBy([
                'customers.id',
                'customers.name',
                'customers.contact',
                'p.name',
                'billings.id', // Menjaga akurasi aggregate data billing
                'billings.total_amount',
                'billings.due_date',
                'billings.paylater_until'
            ])
            ->limit(7);

        $data = $query->get();

        // --- logging ringan ---
        LogHelper::logInfo(
            null,
            [
                'date'  => $today->toDateString(),
                'slug'  => $slug,
                'count' => $data->count(),
            ],
            120,
            '[BillingReminderCollector] Result collected'
        );

        return $data;
    }
}
