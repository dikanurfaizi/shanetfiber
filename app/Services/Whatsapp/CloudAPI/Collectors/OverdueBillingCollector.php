<?php

namespace App\Services\Whatsapp\CloudAPI\Collectors;

use App\Models\Billings\Billing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class OverdueBillingCollector
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
            ->leftJoin('customer_subscriptions as cs', 'customers.id', '=', 'cs.customer_id')
            ->leftJoin('packages as p', 'cs.package_id', '=', 'p.id')

            // --- filter sudah pernah dikirim hari ini ---
            ->leftJoin('message_events as me', function ($join) use ($today, $slug) {
                $join->on('billings.customer_id', '=', 'me.customer_id')
                    ->where('me.message_type', $slug)
                    ->whereDate('me.created_at', $today)
                    ->whereIn('me.status', ['success', 'failed']);
            })

            ->whereNull('me.id')

            // --- rule bisnis reminder ---
            ->where(function ($q) use ($today) {

                // BL : belum jatuh tempo
                $q->where(function ($q) use ($today) {
                    $q->where('billings.status', 'BL')
                        ->whereDate('billings.due_date', '>=', $today);
                })

                    // PL : sudah lewat paylater_until tapi status masih PL
                    ->orWhere(function ($q) use ($today) {
                        $q->where('billings.status', 'PL')
                            ->whereDate('billings.paylater_until', '<', $today);
                    });
            })

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
