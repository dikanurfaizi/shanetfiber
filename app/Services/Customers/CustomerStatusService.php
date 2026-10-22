<?php

namespace App\Services\Customers;

use App\Models\AutoIsolir;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Jobs\Secrets\DisablePppSecretJob;
use App\Jobs\Secrets\EnablePppSecretJob;
use App\Http\Resources\Pppoe\SecretResource;
use App\Services\Routerboard\Ppp\SecretService;
use App\Helpers\LogHelper;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Routerboard\TestConnectionService;
use Illuminate\Support\Collection;

class CustomerStatusService
{
    public function __construct(
        private SecretService $secretService,
        private TestConnectionService $testConnectionService
    ) {}

    public function getSecretsByStatus($router, string $status, string $traceId): array
    {
        try {
            // Jika status 'inactive', ambil daftar secret offline dari MikroTik
            $result = match ($status) {
                'isolir' => $this->getIsolirSecrets($router, $traceId),
                'offline', 'inactive' => $this->getOfflineSecrets($router, $traceId),
                default => $this->getOnlineSecrets($router, $traceId),
            };

            LogHelper::logInfo(null, [
                'router_id' => $router->id,
                'status' => $status,
                'count' => count($result)
            ], 200, 'Successfully retrieved secrets by status', $traceId);

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'exception' => ['message' => $e->getMessage()],
                'router_id' => $router->id ?? null,
                'status' => $status
            ], 500, 'Failed to get secrets by status', $traceId);
            throw $e;
        }
    }

    private function mapSecretsToCustomers(array $secrets, string $traceId): Collection
    {
        return Customer::whereIn('username', $secrets)
            // Load customerSubscriptions beserta package-nya
            ->with(['user', 'customerSubscriptions.package'])
            ->get();
    }

    private function applyAdditionalFilters(Collection $customers, array $filters, string $traceId): Collection
    {
        $status = $filters['status'] ?? null;

        return $customers
            // Filter Alamat
            ->when(isset($filters['search_address']), function ($c) use ($filters) {
                return $filters['search_address'] === 'NULL'
                    ? $c->whereNull('address')
                    : $c->where('address', $filters['search_address']);
            })

            // Filter Admin (User ID)
            ->when(isset($filters['search_admin']), function ($c) use ($filters) {
                return $filters['search_admin'] === 'NULL'
                    ? $c->whereNull('user_id')
                    : $c->where('user_id', $filters['search_admin']);
            })

            ->when($filters['server_search'] ?? null, fn($c, $server) => $c->where('mikrotik_id', $server))

            // Filter Package (Menggunakan Collection filter lewat customerSubscriptions)
            ->when($filters['search_package'] ?? null, function ($c, $packageId) {
                return $c->filter(function ($customer) use ($packageId) {
                    return $customer->customerSubscriptions
                        && $customer->customerSubscriptions->contains('package_id', $packageId);
                });
            })

            // Filter status = 'free' (Menggunakan Collection filter untuk harga paket <= 0)
            ->when(($filters['status'] ?? null) === 'free', function ($c) {
                return $c->filter(function ($customer) {
                    return $customer->customerSubscriptions
                        && $customer->customerSubscriptions->contains(function ($sub) {
                            return $sub->package && $sub->package->price <= 0;
                        });
                });
            })

            // Filter status inactive vs active
            ->when(
                $status === 'inactive',
                fn($c) => $c->where('activation', false),
                fn($c) => $c->where('activation', true)
            )
            ->sortBy('name')
            ->values();
    }

    private function getIsolirSecrets($router, string $traceId): array
    {
        $profileIsolir = AutoIsolir::where('mikrotik_id', $router->id)->value('profile_id');
        if (!$profileIsolir) {
            LogHelper::logError(
                null,
                ['router_id' => $router->id],
                404,
                'Auto isolir not configured for server',
                $traceId
            );
            throw new \RuntimeException('Auto isolir not configured for server ' . $router->name);
        }

        $secrets = $this->secretService->getSecretByProfile($router, $profileIsolir, $traceId);
        return SecretResource::collection($secrets)
            ->where('disabled', 'false')
            ->pluck('name')
            ->toArray();
    }

    private function getOnlineSecrets($router, string $traceId): array
    {
        $activeSecrets = $this->secretService->getPppActive($router, $traceId);
        return SecretResource::collection($activeSecrets)
            ->pluck('name')
            ->toArray();
    }

    private function getOfflineSecrets($router, string $traceId): array
    {
        $online = $this->getOnlineSecrets($router, $traceId);
        $allCustomers = Customer::where('mikrotik_id', $router->id)
            ->where('is_active', true)
            ->pluck('username')
            ->toArray();

        return collect($allCustomers)->diff($online)->values()->all();
    }

    public function processStatusFilter(
        Collection $routers,
        array $searchParams,
        int $perPage,
        string $url,
        string $traceId
    ): array {
        try {
            $status = $searchParams['status'];
            $customers = collect();

            foreach ($routers as $router) {
                $secrets = $this->getSecretsByStatus($router, $status, $traceId);
                $customers = $customers->merge($this->mapSecretsToCustomers($secrets, $traceId));
            }

            $filtered = $this->applyAdditionalFilters($customers, $searchParams, $traceId);
            $paginated = $this->paginateResults($filtered, $perPage, $url, $traceId);

            LogHelper::logInfo(
                null,
                [
                    'status' => $status,
                    'total_customers' => $filtered->count(),
                    'per_page' => $perPage
                ],
                200,
                'Successfully processed status filter',
                $traceId
            );

            return [
                'data' => $paginated,
                'status' => $status,
                'dataCustomers' => $filtered
            ];
        } catch (\Exception $e) {
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
                'Failed to process status filter',
                $traceId
            );
            throw $e;
        }
    }

    private function paginateResults(Collection $items, int $perPage, string $url, string $traceId): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $sliced = $items->slice(($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator(
            $sliced,
            $items->count(),
            $perPage,
            $page,
            ['path' => $url]
        );
    }

    public function disableCustomer(Customer $customer, string $traceId): array
    {
        $router = Mikrotik::find($customer->mikrotik_id);

        try {
            // Dispatch job async
            DisablePppSecretJob::dispatch(
                $router,
                $customer->username,
                $customer->id,
                $traceId
            )->onQueue('default');


            LogHelper::logInfo(
                null,
                ['customer_id' => $customer->id],
                200,
                'Disable request queued successfully',
                $traceId
            );

            return ['success' => 'Disable request has been queued'];
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => $e->getMessage(),
                    'customer_id' => $customer->id
                ],
                503,
                'Router offline',
                $traceId
            );
            return ['error' => 'Router offline'];
        }
    }

    public function forceDisableCustomer(Customer $customer, string $traceId): array
    {
        $customer->update(['is_active' => false]);
        LogHelper::logWarning(
            null,
            ['customer_id' => $customer->id],
            200,
            'Force disabled customer (not on server)',
            $traceId
        );
        return ['success' => 'Disable customer successfully but not in server'];
    }

    public function enableCustomer(Customer $customer, string $traceId): array
    {
        $router = Mikrotik::find($customer->mikrotik_id);

        try {
            // Dispatch async job
            EnablePppSecretJob::dispatch(
                $router,
                $customer->username,
                $customer->id,
                $traceId
            )->onQueue('default');

            LogHelper::logInfo(
                null,
                ['customer_id' => $customer->id],
                200,
                'Enable request queued successfully',
                $traceId
            );

            return ['success' => 'Enable request has been queued'];
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'router_id' => $router->id ?? null
                ],
                503,
                'Router offline during enable attempt',
                $traceId
            );
            return ['error' => 'Router offline, please check your router.'];
        }
    }

    public function forceEnableCustomer(Customer $customer, string $traceId): array
    {
        $customer->update(['is_active' => true, 'activation' => false]);
        LogHelper::logWarning(
            null,
            ['customer_id' => $customer->id],
            200,
            'Force enabled customer (not on server)',
            $traceId
        );
        return ['success' => 'Enable customer successfully but not in server'];
    }
}
