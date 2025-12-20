<?php

namespace App\Services\Customers;

use App\Models\Customers\Customer;
use App\Models\Billings\BillingPeriode;
use App\Models\Packages\Package;
use App\Models\Mikrotik;
use App\Models\User;
use App\Helpers\LogHelper;

class CustomerQueryService
{
    public function getTrashedCustomers(array $searchParams, int $perPage = 25, string $traceId): array
    {
        try {
            // Log the start of operation with all search parameters
            $activeFilters = array_filter($searchParams, fn($value) => !is_null($value));
            LogHelper::logInfo(
                null,
                [
                    'active_filters' => $activeFilters,
                    'per_page' => $perPage
                ],
                200,
                'Starting trashed customers retrieval with filters',
                $traceId
            );

            $query = Customer::onlyTrashed()
                ->with(['subscriptions.package', 'user']);

            // Apply all filters in one go
            $query->when(true, function ($query) use ($searchParams) {
                if (!empty($searchParams['search_name'])) {
                    $query->where('name', 'like', "%{$searchParams['search_name']}%");
                }

                if (!empty($searchParams['search_address'])) {
                    $searchParams['search_address'] === 'NULL'
                        ? $query->whereNull('address')
                        : $query->where('address', $searchParams['search_address']);
                }

                if (!empty($searchParams['search_admin'])) {
                    $searchParams['search_admin'] === 'NULL'
                        ? $query->whereNull('user_id')
                        : $query->where('user_id', $searchParams['search_admin']);
                }

                if (!empty($searchParams['server_search'])) {
                    $query->where('mikrotik_id', $searchParams['server_search']);
                }

                if (!empty($searchParams['search_package'])) {
                    $query->whereHas('subscriptionsWithTrashed', function ($q) use ($searchParams) {
                        $q->where('package_id', $searchParams['search_package']);
                    });
                }

                if (!empty($searchParams['search_membership'])) {
                    $query->whereHas('membershipWithTrashed', function ($q) use ($searchParams) {
                        $q->where('membership_id', $searchParams['search_membership']);
                    });
                }
            })->orderBy('name');

            // Execute queries
            $customers = $query->paginate($perPage);
            $addresses = Customer::onlyTrashed()->select('address')->distinct()->pluck('address', 'address');
            $servers = Mikrotik::where('disabled', 'false')->orderBy('name')->pluck('name', 'id');
            $admins = User::where('disabled', 'false')->orderBy('name')->pluck('name', 'id');
            $periodeName = BillingPeriode::latest('created_at')->value('name');
            $allPackages = Package::withTrashed()->orderBy('name')->get();

            // Log successful results
            LogHelper::logInfo(
                null,
                [
                    'results' => [
                        'customers' => $customers->total(),
                        'address_options' => count($addresses),
                        'server_options' => count($servers),
                        'admin_options' => count($admins)
                    ]
                ],
                200,
                'Successfully retrieved trashed customers',
                $traceId
            );

            return [
                'customers' => $customers,
                'addresses' => $addresses,
                'servers' => $servers,
                'admins' => $admins,
                'periodeName' => $periodeName,
                'perPage' => $perPage,
                'allPackages' => $allPackages
            ];
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'search_params' => $searchParams
                ],
                500,
                'Failed to retrieve trashed customers',
                $traceId
            );

            return [
                'customers' => collect([]),
                'addresses' => collect([]),
                'servers' => collect([]),
                'admins' => collect([]),
                'periodeName' => '',
                'perPage' => $perPage
            ];
        }
    }
}
