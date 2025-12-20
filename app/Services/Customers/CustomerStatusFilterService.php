<?php

namespace App\Services\Customers;

use App\Helpers\LogHelper;
use Illuminate\Support\Collection;

class CustomerStatusFilterService
{

    /**
     * =============================
     *   HELPER: APPLY STATUF FILTER
     * =============================
     */
    public function applyStatusFilter($query, string $status, string $traceId)
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

    public function getFilteredCustomersByStatus(Collection $customers, array $filters, string $traceId): Collection
    {
        try {
            $filtered = $customers
                ->when($filters['search_address'] === 'NULL', fn($c) => $c->whereNull('address'))
                ->when($filters['search_address'] ?? null, fn($c, $addr) => $c->where('address', $addr))
                ->when($filters['server_search'] ?? null, fn($c, $server) => $c->where('mikrotik_id', $server))
                ->when($filters['search_package'] ?? null, fn($c, $package) => $c->where('package_id', $package))
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
}
