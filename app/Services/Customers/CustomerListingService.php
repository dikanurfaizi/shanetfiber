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
                ->when($filters['search_name'] ?? null, fn($q, $name) => $q->where('name', 'like', "%{$name}%"))

                // Filter Alamat
                ->when(isset($filters['search_address']), function ($q) use ($filters) {
                    if ($filters['search_address'] === 'NULL') {
                        $q->whereNull('address');
                    } else {
                        $q->where('address', $filters['search_address']);
                    }
                })

                // Filter Admin (User ID)
                ->when(isset($filters['search_admin']), function ($q) use ($filters) {
                    if ($filters['search_admin'] === 'NULL') {
                        $q->whereNull('user_id');
                    } else {
                        $q->where('user_id', $filters['search_admin']);
                    }
                })

                ->when($filters['status'] ?? null, fn($q, $status) => $statusFilter->applyStatusFilter($q, $status, $traceId))
                ->when($filters['server_search'] ?? null, fn($q, $server) => $q->where('mikrotik_id', $server))
                ->when($filters['search_package'] ?? null, function ($q, $packageId) {
                    $q->whereHas('activeSubscription', fn($sq) => $sq->where('package_id', $packageId));
                })

                // Filter status = 'free' (Cek harga paket <= 0 via relasi activeSubscription)
                ->when(($filters['status'] ?? null) === 'free', function ($q) {
                    $q->whereHas('activeSubscription', function ($subQuery) {
                        $subQuery->whereHas('package', function ($pkgQuery) {
                            $pkgQuery->where('price', '<=', 0);
                        });
                    });
                })
                ->orderBy('name');

            $result = $query->paginate($perPage);

            LogHelper::logInfo(
                null,
                [
                    'filters' => $filters,
                    'per_page' => $perPage,
                    'current_page' => $result->currentPage(),
                    'total_data' => $result->total(),
                    'total_pages' => $result->lastPage(),
                    'sql_query' => $query->toSql(),
                    'bindings' => $query->getBindings(),
                ],
                200,
                'Successfully retrieved paginated customers',
                $traceId,
                $timer->lap()
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
                    'total_results' => $results->count(),
                    'total_pages' => $paginator->lastPage(),
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $perPage,
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

    public function getActiveServers($server, string $traceId): Collection
    {
        $timer = new ExecutionTimer();
        try {
            $servers = Mikrotik::where('disabled', 'false')->orderBy('name')->pluck('name', 'id');

            LogHelper::logInfo(
                null,
                [
                    'requested_server_filter' => $server,
                    'server_count' => $servers->count(),
                ],
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
