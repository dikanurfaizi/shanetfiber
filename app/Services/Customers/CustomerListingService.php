<?php

namespace App\Services\Customers;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class CustomerListingService
{
    public function getPaginatedCustomers(array $filters, int $perPage, string $traceId): LengthAwarePaginator
    {
        $timer = new ExecutionTimer();
        $statusFilter = new CustomerStatusFilterService();
        try {
            $query = Customer::with(['user'])
                ->when($filters['search_name'] ?? null, fn($query, $name) => $query->where('name', 'like', "%{$name}%"))
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
                ->when($filters['status'] ?? null, fn($query, $status) => $statusFilter->applyStatusFilter($query, $status, $traceId))
                ->when($filters['server_search'] ?? null, fn($query, $server) => $query->where('mikrotik_id', $server))
                ->when($filters['search_package'] ?? null, fn($query, $package) => $query->where('package_id', $package))
                ->orderBy('name');

            $result = $query->paginate($perPage);

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
                $traceId,
                $timer->lap()
            );
            throw $e;
        }
    }

    public function paginateResults(Collection $results, int $perPage, Request $request, string $traceId): LengthAwarePaginator
    {
        $timer = new ExecutionTimer();
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
                $traceId,
                $timer->lap()
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
                $traceId,
                $timer->lap()
            );
            throw $e;
        }
    }

    public function getCustomerAddresses(string $traceId): Collection
    {
        $timer = new ExecutionTimer();
        try {
            $addresses = Customer::select('address')->distinct()->pluck('address', 'address');

            LogHelper::logInfo(
                null,
                ['address_count' => $addresses->count()],
                200,
                'Successfully retrieved customer addresses',
                $traceId,
                $timer->lap()
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
                $traceId,
                $timer->lap()
            );
            throw $e;
        }
    }

    public function getActiveServers(string $traceId): Collection
    {
        $timer = new ExecutionTimer();
        try {
            $servers = Mikrotik::where('disabled', 'false')->orderBy('name')->pluck('name', 'id');

            LogHelper::logInfo(
                null,
                ['server_count' => $servers->count()],
                200,
                'Successfully retrieved active servers',
                $traceId,
                $timer->lap()
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
                $traceId,
                $timer->lap()
            );
            throw $e;
        }
    }

    public function getActiveAdmins(string $traceId): Collection
    {
        $timer = new ExecutionTimer();
        try {
            $admins = User::where('disabled', 'false')->orderBy('name')->pluck('name', 'id');

            LogHelper::logInfo(
                null,
                ['admin_count' => $admins->count()],
                200,
                'Successfully retrieved active admins',
                $traceId,
                $timer->lap()
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
                $traceId,
                $timer->lap()
            );
            throw $e;
        }
    }
}
