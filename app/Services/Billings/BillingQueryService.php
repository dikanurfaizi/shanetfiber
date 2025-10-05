<?php

namespace App\Services\Billings;

use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\UserAuth;
use App\Helpers\LogHelper;

class BillingQueryService
{
    public function buildBillingQuery(Request $request, string $traceId, bool $showAllPeriods = false)
    {
        $query = Billing::select('billings.*')
            ->with([
                'customer',
                'receipt',
                'billingSubscription',
                'billingSubscription.billingPeriode'
            ]);


        // filter by status
        if ($request->filled('status')) {
            $query->where('billings.status', $request->status);
        }

        if ($request->filled('periode_id')) {
            $query->whereHas('billingSubscription.billingPeriode', function ($q) use ($request) {
                $q->where('id', $request->periode_id);
            });
        }

        $query = $this->applySearchFilters($query, $request, $traceId, $showAllPeriods);
        $query = $this->applyOrdering($query);

        return $query;
    }

    private function applySearchFilters($query, Request $request, string $traceId, bool $showAllPeriods = false)
    {
        $searchParams = $this->prepareSearchParams($request, $showAllPeriods);

        // Filter berdasarkan kolom di tabel billings
        if ($searchParams['name']) {
            $query->where('customer_name', 'like', "%{$searchParams['name']}%");
        }

        // Filter berdasarkan relasi customer slug
        if ($searchParams['customer_slug']) {
            $slug = $searchParams['customer_slug'];
            $query->whereHas('customer', function ($q) use ($slug) {
                if ($slug === null) {
                    $q->whereNull('slug');
                } else {
                    $q->where('slug', $slug);
                }
            });
        }

        // Filter berdasarkan relasi customer address
        if ($searchParams['address']) {
            $address = $searchParams['address'];
            $query->whereHas('customer', function ($q) use ($address) {
                if ($address === 'no-address') {
                    $q->whereNull('address');
                } else {
                    $q->where('address', $address);
                }
            });
        }

        // Filter berdasarkan periode - handle null billingSubscription
        if ($searchParams['periode'] && !$searchParams['show_all_periods']) {
            $periode = $searchParams['periode'];
            $query->where(function ($q) use ($periode) {
                $q->whereHas('billingSubscription.billingPeriode', function ($q) use ($periode) {
                    $q->where('name', 'like', "%{$periode}%");
                });
            });
        }

        // Filter status dan due date di tabel billings
        if (!is_null($searchParams['status'])) {
            $this->applyStatusFilter($query, $searchParams['status'], $searchParams['duedate'], $traceId);
        }

        // Filter user_id melalui relasi customer.user_id
        if ($searchParams['user_id']) {
            $userId = $searchParams['user_id'];
            $query->whereHas('customer', function ($q) use ($userId) {
                if ($userId === 'Not Linked User' || $userId === 0) {
                    $q->whereNull('user_id');
                } else {
                    $q->where('user_id', $userId);
                }
            });
        }

        return $query;
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

        // Konversi admin_name (ID -> nama user)
        $searchUserId = null;
        if ($request->filled('admin_name')) {
            $userById = User::where('name', $request->admin_name)->first();
            $searchUserId = $userById?->id;
        }

        // Fallback: jika tidak ada dari request, pakai user yang login jika punya izin
        if (empty($searchUserId) && $user && $user->can('view-customers-widget-related')) {
            $searchUserId = $auth['userId'];
        }

        // Hanya set default periode jika tidak menampilkan semua periode
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
            'show_all_periods' => $showAllPeriods, // Tambahkan flag ini
        ];
    }

    private function applyStatusFilter($query, $status, $duedate, string $traceId)
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
                $field = $statuses[0] === 'LS' ? 'payment_time' : 'pay_later';
                $query->where($field, 'like', "%{$duedate}%");
            }
        } else {
            LogHelper::logInfo(null, [], 404, '[Billing Index] Status filter not applied', $traceId);
        }
    }

    private function applyOrdering($query)
    {
        $query->leftJoin('billing_subscription_periodes as bsp', 'billings.billing_subscription_id', '=', 'bsp.id')
            ->leftJoin('billing_periodes', 'bsp.billing_periode_id', '=', 'billing_periodes.id')
            ->leftJoin('customers', 'billings.customer_id', '=', 'customers.id')
            ->orderByDesc('billing_periodes.created_at')
            ->orderBy('customers.name', 'asc')
            ->select('billings.*'); // Pastikan select hanya kolom billing

        return $query;
    }

    private function getUserAuthData()
    {
        return UserAuth::getUserDetails();
    }

    public function getStatuses()
    {
        $statusLabels = [
            'LS' => 'PAID',
            'BL' => 'UNPAID',
            'NP' => 'NOT ACTIVE PERIOD',
            'PL' => 'PAYLATER',
        ];

        $raw = Billing::select('status')->distinct()->pluck('status');

        return $raw->mapWithKeys(fn($st) => [$st => $statusLabels[$st] ?? $st]);
    }
}
