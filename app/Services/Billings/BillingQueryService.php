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
    public function buildBillingQuery(Request $request, string $traceId)
    {
        $query = Billing::with(['receipt', 'billing_periode', 'customer']);

        $this->applySearchFilters($query, $request, $traceId);
        $this->applyOrdering($query);

        return $query;
    }

    private function applySearchFilters($query, Request $request, string $traceId)
    {
        $searchParams = $this->prepareSearchParams($request);

        // Apply name filter
        if ($searchParams['name']) {
            $query->where('customer_name', 'like', "%{$searchParams['name']}%");
        }

        // Apply customer slug filter
        if ($searchParams['customer_slug']) {
            $query->whereHas('customer', fn($q) => $q->where('slug', $searchParams['customer_slug']));
        }

        // Apply address filter
        if ($searchParams['address']) {
            $query->whereHas('customer', function ($q) use ($searchParams) {
                $searchParams['address'] === 'no-address'
                    ? $q->whereNull('address')
                    : $q->where('address', $searchParams['address']);
            });
        }

        // Apply periode filter
        if ($searchParams['periode']) {
            $query->whereHas('billing_periode', fn($q) => $q->where('name', 'like', "%{$searchParams['periode']}%"));
        }

        // Apply status filter
        if (!is_null($searchParams['status'])) {
            $this->applyStatusFilter($query, $searchParams['status'], $searchParams['duedate'], $traceId);
        }

        // Apply user filter
        if ($searchParams['user_id']) {
            $this->applyUserFilter($query, $searchParams['user_id']);
        }
    }

    public function prepareSearchParams(Request $request): array
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
            $userById = User::find($request->admin_name);
            $searchUserId = $userById?->name;
        }

        // Fallback: jika tidak ada dari request, pakai user yang login jika punya izin
        if (empty($searchUserId) && $user && $user->can('view-customers-widget-related')) {
            $searchUserId = auth()->user()->name;
        }

        // Default periode jika tidak ada filter
        if (!$customerSlug && !$searchName && !$searchMonth && !$searchYear && !$searchStatus && !$searchAddress && !$searchDuedate) {
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
        ];
    }

    private function applyStatusFilter($query, $status, $duedate, string $traceId)
    {
        $statuses = explode('-', $status);
        $excludes = array_filter($statuses, fn($s) => str_starts_with($s, '!'));

        if (count($excludes) > 0) {
            $excludeValues = array_map(fn($s) => ltrim($s, '!'), $excludes);
            $query->whereNotIn('status', $excludeValues);

            if ($duedate) {
                $query->where('due_date', 'like', "%{$duedate}%");
            }
        } elseif (count($statuses) === 1) {
            $query->where('status', $statuses[0]);

            if ($duedate) {
                $field = $statuses[0] === 'LS' ? 'payment_time' : 'pay_later';
                $query->where($field, 'like', "%{$duedate}%");
            }
        } else {
            LogHelper::logInfo(null, [], 404, '[Billing Index] Status filter not applied', $traceId);
        }
    }

    private function applyUserFilter($query, $userId)
    {
        $query->whereHas('customer', function ($q) use ($userId) {
            $q->whereColumn('billings.customer_id', 'customers.id')
                ->whereNull('customers.deleted_at');

            if ($userId === 'Not Linked User' || $userId === 0) {
                $q->whereNull('customers.user_id');
            } else {
                $q->where('customers.user_id', function ($subQuery) use ($userId) {
                    $subQuery->select('id')->from('users')->where('name', $userId)->limit(1);
                });
            }
        });
    }

    private function applyOrdering($query)
    {
        $query->orderBy(
            BillingPeriode::select('created_at')
                ->whereColumn('billing_periodes.id', 'billings.billing_periode_id'),
            'DESC'
        )->orderBy('customer_name', 'ASC');
    }

    private function getUserAuthData()
    {
        return UserAuth::getUserDetails();
    }
}
