<?php

namespace App\Services\WhatsApp\CloudAPI\Collectors;

use App\Models\Billings\Billing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class OverdueBillingCollector
{
    public function collect(Carbon $today, string ...$slugs): Collection
    {
        $todayStr = $today->toDateString();

        $query = Billing::query()
            ->select([
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.user_id as user_id',
                'p.name as package_name',
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

            // 👉 SOLUSI UTAMA: Mengunci anti-duplikasi menggunakan WHERE NOT EXISTS
            // Menjamin data yang sudah sukses, failed, atau pending hari ini dengan tipe slug tersebut tidak akan pernah lolos query.
            ->whereNotExists(function ($q) use ($slugs, $todayStr) {
                $q->select(DB::raw(1))
                    ->from('message_events as me')
                    ->whereColumn('me.customer_id', 'billings.customer_id')
                    ->whereIn('me.message_type', $slugs)
                    ->whereDate('me.created_at', $todayStr)
                    ->whereIn('me.status', ['success', 'failed', 'pending']);
            })

            // --- rule bisnis overdue ---
            ->where(function ($q) use ($todayStr) {
                // Status BL (Billing)
                $q->where(function ($subQ) use ($todayStr) {
                    $subQ->where('billings.status', 'BL')
                        ->whereDate('billings.due_date', '<', $todayStr);
                })
                    // Status PL (Paylater)
                    ->orWhere(function ($subQ) use ($todayStr) {
                        $subQ->where('billings.status', 'PL')
                            ->whereDate('billings.paylater_until', '<', $todayStr);
                    });
            })

            // Leburkan data billing ID agar me-return tepat 1 baris per customer
            ->groupBy([
                'customers.id',
                'customers.name',
                'customers.contact',
                'customers.user_id',
                'p.name'
            ]);

        // 💡 Diperbaiki: ->limit(10) telah dihapus total agar mengambil seluruh data
        $data = $query->get();

        // --- logging status ---
        LogHelper::logInfo(
            null,
            [
                'date'   => $todayStr,
                'slugs'  => $slugs,
                'count'  => $data->count(),
            ],
            120,
            '[OverdueBillingCollector] All matching overdue results collected successfully without throttling'
        );

        return $data;
    }
}
