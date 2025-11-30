<?php

namespace App\Services\Customers;

use App\Helpers\LogHelper;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerDataService
{
    public function getPaginatedCustomers(array $filters, int $perPage, string $traceId): LengthAwarePaginator
    {
        try {
            $query = Customer::with(['user'])
                ->when($filters['search_name'] ?? null, fn ($query, $name) => $query->where('name', 'like', "%{$name}%"))
                ->when($filters['search_address'] ?? null, function ($query, $address) {
                    if ($address === 'NULL') {
                        $query->whereNull('address');
                    } else {
                        $query->where('address', $address);
                    }
                })
                ->when($filters['search_admin'] ?? null, function ($query, $admin) {
                    if ($admin === 'NULL') {
                        $query->whereNull('user_id');
                    } else {
                        $query->where('user_id', $admin);
                    }
                })
                ->when($filters['status'] ?? null, fn ($query, $status) => $this->applyStatusFilter($query, $status, $traceId))
                ->when($filters['server_search'] ?? null, fn ($query, $server) => $query->where('mikrotik_id', $server))
                ->when($filters['search_package'] ?? null, fn ($query, $package) => $query->where('package_id', $package))
                ->orderBy('name');

            $result = $query->paginate($perPage);

            LogHelper::logInfo(
                null,
                [
                    'total_results' => $result->total(),
                    'current_page' => $result->currentPage(),
                    'per_page' => $result->perPage(),
                ],
                200,
                'Successfully retrieved paginated customers',
                $traceId
            );

            return $result;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    'filters' => $filters,
                    'per_page' => $perPage,
                ],
                500,
                'Failed to get paginated customers',
                $traceId
            );
            throw $e;
        }
    }

    public function getFilteredCustomersByStatus(Collection $customers, array $filters, string $traceId): Collection
    {
        try {
            $filtered = $customers
                ->when($filters['search_address'] === 'NULL', fn ($c) => $c->whereNull('address'))
                ->when($filters['search_address'] ?? null, fn ($c, $addr) => $c->where('address', $addr))
                ->when($filters['server_search'] ?? null, fn ($c, $server) => $c->where('mikrotik_id', $server))
                ->when($filters['search_package'] ?? null, fn ($c, $package) => $c->where('package_id', $package))
                ->where('activation', 'true')
                ->sortBy('name')
                ->values();

            LogHelper::logInfo(
                null,
                [
                    'filtered_count' => $filtered->count(),
                ],
                200,
                'Successfully filtered customers by status',
                $traceId
            );

            return $filtered;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    'initial_count' => $customers->count(),
                    'filters' => $filters,
                ],
                500,
                'Failed to filter customers by status',
                $traceId
            );
            throw $e;
        }
    }

    public function paginateResults(Collection $results, int $perPage, Request $request, string $traceId): LengthAwarePaginator
    {
        try {
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentPageResults = $results->slice(($currentPage - 1) * $perPage, $perPage)->all();

            $paginator = new LengthAwarePaginator(
                $currentPageResults,
                $results->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->except('page')]
            );

            LogHelper::logInfo(
                null,
                [
                    'total_pages' => $paginator->lastPage(),
                    'current_page' => $paginator->currentPage(),
                ],
                200,
                'Successfully paginated results',
                $traceId
            );

            return $paginator;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    'total_results' => $results->count(),
                    'per_page' => $perPage,
                ],
                500,
                'Failed to paginate results',
                $traceId
            );
            throw $e;
        }
    }

    private function applyStatusFilter($query, string $status, string $traceId)
    {
        try {
            return match ($status) {
                'active' => $query->where('activation', 'true')->where('disabled', 'false'),
                'inactive' => $query->where('activation', 'false')->where('disabled', 'true'),
                default => $query,
            };
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    'status_filter' => $status,
                ],
                500,
                'Failed to apply status filter',
                $traceId
            );
            throw $e;
        }
    }

    public function getCustomerDetails(Customer $customer, string $traceId): array
    {
        try {
            $billingPeriodeActive = BillingPeriode::latest('created_at')->first();

            // Ambil semua billing customer dengan relasi
            $billings = Billing::with([
                'billingSubscription.billingPeriode', // relasi ke periode
                'customer',                          // relasi ke customer
            ])
                ->where('customer_id', $customer->id)
                ->orderByDesc('created_at')
                ->get();

            $billingLastPayment = $billings->where('status', 'LS')
                ->sortByDesc('payment_time')
                ->first();

            $lastPayment = 0;
            if ($billingLastPayment && $billingLastPayment->billingSubscription) {
                if ($billingLastPayment->billingSubscription->billing_periode_id == $billingPeriodeActive->id) {
                    $lastPayment = $billingLastPayment->total_amount;
                }
            }

            $latestBilling = $billings->first(); // billing terbaru
            $unpaid = $billings->where('status', '!=', 'LS');

            $result = [
                'billingPeriodeActive' => $billingPeriodeActive,
                'billingLastPayment' => $billingLastPayment,
                'lastPayment' => $lastPayment,
                'billingPeriods' => $latestBilling?->billingSubscription?->billingPeriode?->name,
                'totalAmount' => $latestBilling?->total_amount,
                'discount' => $latestBilling?->discount_amount,
                'dueDate' => $latestBilling?->due_date,
                'notYetPaid' => $unpaid->sum('total_amount'),
            ];

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'billingPeriodeActive' => $result['billingPeriodeActive'],
                    'billingLastPayment' => $result['billingLastPayment'],
                    'lastPayment' => $result['lastPayment'],
                    'billingPeriods' => $result['billingPeriods'],
                    'totalAmount' => $result['totalAmount'],
                    'discount' => $result['discount'],
                    'dueDate' => $result['dueDate'],
                    'notYetPaid' => $result['notYetPaid'],
                ],
                200,
                'Successfully retrieved customer details',
                $traceId
            );

            return $result;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    'customer_id' => $customer->id,
                ],
                500,
                'Failed to get customer details',
                $traceId
            );
            throw $e;
        }
    }

    public function getCustomerAddresses(string $traceId): Collection
    {
        try {
            $addresses = Customer::select('address')->distinct()->pluck('address', 'address');

            LogHelper::logInfo(
                null,
                ['address_count' => $addresses->count()],
                200,
                'Successfully retrieved customer addresses',
                $traceId
            );

            return $addresses;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                ],
                500,
                'Failed to get customer addresses',
                $traceId
            );
            throw $e;
        }
    }

    public function getActiveServers(string $traceId): Collection
    {
        try {
            $servers = Mikrotik::where('disabled', 'false')->orderBy('name')->pluck('name', 'id');

            LogHelper::logInfo(
                null,
                ['server_count' => $servers->count()],
                200,
                'Successfully retrieved active servers',
                $traceId
            );

            return $servers;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                ],
                500,
                'Failed to get active servers',
                $traceId
            );
            throw $e;
        }
    }

    public function getActiveAdmins(string $traceId): Collection
    {
        try {
            $admins = User::where('disabled', 'false')->orderBy('name')->pluck('name', 'id');

            LogHelper::logInfo(
                null,
                ['admin_count' => $admins->count()],
                200,
                'Successfully retrieved active admins',
                $traceId
            );

            return $admins;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                ],
                500,
                'Failed to get active admins',
                $traceId
            );
            throw $e;
        }
    }

    public function getCurrentBillingPeriod(string $traceId): string
    {
        try {
            $period = BillingPeriode::latest('created_at')->value('name');

            LogHelper::logInfo(
                null,
                ['period_name' => $period],
                200,
                'Successfully retrieved current billing period',
                $traceId
            );

            return $period;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                ],
                500,
                'Failed to get current billing period',
                $traceId
            );
            throw $e;
        }
    }
}
