<?php

namespace App\Services\Billings;

use App\Helpers\ExecutionTimer;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\User;
use App\Models\InvestorAdmin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\UserAuth;
use App\Helpers\LogHelper;

class BillingQueryService
{
    public function buildBillingQuery(Request $request, string $traceId, bool $showAllPeriods = false)
    {
        $timer = new ExecutionTimer();

        // 1. Mulai Query dasar & Eager Loading relasi yang dibutuhkan untuk View
        $query = Billing::select('billings.*')
            ->with([
                'customer',
                'billingSubscription.billingPeriode',
                'billingPayment'
            ]);

        // 2. Lakukan Left Join di awal agar sub-fungsi bisa langsung menggunakan kolom tabel relasi (Lebih Cepat dari whereHas)
        $query->leftJoin('billing_subscription_periodes as bsp', 'billings.billing_subscription_id', '=', 'bsp.id')
            ->leftJoin('billing_periodes', 'bsp.billing_periode_id', '=', 'billing_periodes.id')
            ->leftJoin('customers', 'billings.customer_id', '=', 'customers.id');

        // Filter langsung berdasarkan status utama
        if ($request->filled('status')) {
            $query->where('billings.status', $request->status);
        }

        // Optimasi: Ganti whereHas dengan langsung tembak kolom bsp dari hasil join
        if ($request->filled('periode_id')) {
            $query->where('bsp.billing_periode_id', $request->periode_id);
        }

        // Jalankan filter pencarian & ordering internal
        $searchParams = $this->applySearchFilters($query, $request, $showAllPeriods);
        $this->applyOrdering($query);

        // 3. Log Tunggal - Simpel, Bersih, dan Informatif untuk Production
        LogHelper::logInfo(null, [
            'user_id' => $this->getUserAuthData()['userId'] ?? null,
            'active_filters' => array_filter($searchParams),
            'status_direct' => $request->status
        ], 200, "[BillingQueryService] Query built successfully", $traceId, $timer->lap());

        return $query;
    }

    private function applySearchFilters($query, Request $request, bool $showAllPeriods = false): array
    {
        $searchParams = $this->prepareSearchParams($request, $showAllPeriods);

        $hasFilter =
            $request->filled('status') ||
            $searchParams['name'] ||
            !is_null($searchParams['status']) ||
            $searchParams['user_id'] ||
            $searchParams['customer_slug'];

        if (!$hasFilter) {
            $query->where('billings.status', '!=', 'VD');
        }

        // Optimasi: Menggunakan kolom tabel hasil join langsung (customers.name)
        if ($searchParams['name']) {
            $query->where('customers.name', 'like', "%{$searchParams['name']}%");
        }

        if ($searchParams['customer_slug']) {
            if ($searchParams['customer_slug'] === null) {
                $query->whereNull('customers.slug');
            } else {
                $query->where('customers.slug', $searchParams['customer_slug']);
            }
        }

        if ($searchParams['address']) {
            if ($searchParams['address'] === 'no-address') {
                $query->whereNull('customers.address');
            } else {
                $query->where('customers.address', $searchParams['address']);
            }
        }

        // Optimasi: Menggunakan kolom tabel hasil join langsung (billing_periodes.name)
        if ($searchParams['periode'] && !$searchParams['show_all_periods']) {
            $query->where('billing_periodes.name', 'like', "%{$searchParams['periode']}%");
        }

        if (!is_null($searchParams['status'])) {
            $this->applyStatusFilter($query, $searchParams['status'], $searchParams['duedate']);
        }

        if ($searchParams['user_id']) {
            if ($searchParams['user_id'] === 'Not Linked User' || $searchParams['user_id'] === 0) {
                $query->whereNull('customers.user_id');
            } else {
                $query->where('customers.user_id', $searchParams['user_id']);
            }
        }

        // === 🔒 Role-based visibility (Optimasi: Gunakan kolom tabel hasil join langsung) ===
        $auth = $this->getUserAuthData();
        $user = $auth['user'];

        if (!$user->can('view-bill-top-bar-total')) {
            if ($user->can('investor-billing-total')) {
                $linkedAdminIds = InvestorAdmin::where('investor_id', $user->id)->pluck('admin_id')->toArray();
                $visibleUserIds = array_merge([$user->id], $linkedAdminIds);

                $query->whereIn('customers.user_id', $visibleUserIds);
            } else {
                $query->where('customers.user_id', $user->id);
            }
        }

        return $searchParams;
    }

    public function prepareSearchParams(Request $request, bool $showAllPeriods = false): array
    {
        $auth = $this->getUserAuthData();
        $user = $auth['user'];

        $searchName = $request->search_name;
        $searchMonth = $request->search_month;
        $searchYear = $request->search_year;
        $searchPeriode = ($searchMonth && $searchYear) ? "{$searchMonth} {$searchYear}" : null;
        $searchStatus = $request->search_status;
        $searchAddress = $request->search_address;
        $searchDuedate = $request->duedate;
        $customerSlug = $request->customer;

        $searchUserId = null;
        if ($request->filled('admin_name')) {
            $userById = User::where('name', $request->admin_name)->first();
            $searchUserId = $userById?->id;
        }

        if (empty($searchUserId) && $user && $user->can('view-customers-widget-related')) {
            $searchUserId = $auth['userId'];
        }

        if (!$showAllPeriods && !$customerSlug && !$searchName && !$searchMonth && !$searchYear && !$searchStatus && !$searchAddress && !$searchDuedate) {
            $latestPeriode = BillingPeriode::latest('created_at')->value('name');
            $searchPeriode = $latestPeriode;
            $searchMonth = Carbon::parse($latestPeriode)->format('F');
            $searchYear = Carbon::parse($latestPeriode)->format('Y');
        }

        return [
            'name' => $searchName,
            'customer_slug' => $customerSlug,
            'address' => $searchAddress,
            'periode' => $searchPeriode,
            'status' => $searchStatus,
            'duedate' => $searchDuedate,
            'user_id' => $searchUserId,
            'month' => $searchMonth,
            'year' => $searchYear,
            'show_all_periods' => $showAllPeriods,
        ];
    }

    private function applyStatusFilter($query, $status, $duedate)
    {
        $statuses = explode('-', $status);
        $excludes = array_filter($statuses, fn($s) => str_starts_with($s, '!'));

        if (count($excludes) > 0) {
            $excludeValues = array_map(fn($s) => ltrim($s, '!'), $excludes);
            $query->whereNotIn('billings.status', $excludeValues);

            if ($duedate) {
                $query->where('billings.due_date', 'like', "%{$duedate}%");
            }
        } elseif (count($statuses) === 1) {
            $query->where('billings.status', $statuses[0]);

            if ($duedate) {
                $field = $statuses[0] === 'LS' ? 'billings.payment_time' : 'billings.pay_later';
                $query->where($field, 'like', "%{$duedate}%");
            }
        }
    }

    private function applyOrdering($query)
    {
        // Tinggal menerapkan aturan ordering karena Join sudah dilakukan di atas.
        $query->orderByDesc('billing_periodes.created_at')
            ->orderBy('customers.name', 'asc');
    }

    private function getUserAuthData()
    {
        return UserAuth::getUserDetails();
    }

    public function getStatuses(string $traceId)
    {
        $timer = new ExecutionTimer();

        $statusLabels = [
            'LS' => 'PAID',
            'BL' => 'UNPAID',
            'VD' => 'VOID',
            'PL' => 'PAYLATER',
        ];

        $raw = Billing::select('status')->distinct()->pluck('status');
        $result = $raw->mapWithKeys(fn($st) => [$st => $statusLabels[$st] ?? $st]);

        LogHelper::logInfo(null, $result->toArray(), 200, "[BillingQueryService] Successfully retrieved billing statuses", $traceId, $timer->lap());

        return $result;
    }
}
