<?php

namespace App\Services\Customers;

use App\Models\AutoIsolir;
use App\Models\Customer;
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
            $result = match ($status) {
                'isolir' => $this->getIsolirSecrets($router, $traceId),
                'offline' => $this->getOfflineSecrets($router, $traceId),
                default => $this->getOnlineSecrets($router, $traceId),
            };

            LogHelper::logInfo(
                null,
                [
                    'router_id' => $router->id,
                    'status' => $status,
                    'count' => count($result)
                ],
                200,
                'Successfully retrieved secrets by status',
                $traceId
            );

            return $result;
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
                    'router_id' => $router->id ?? null,
                    'status' => $status
                ],
                500,
                'Failed to get secrets by status',
                $traceId
            );
            throw $e;
        }
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
            ->where('disabled', 'false')
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

    private function mapSecretsToCustomers(array $secrets, string $traceId): Collection
    {
        return Customer::whereIn('username', $secrets)
            ->with(['user', 'package'])
            ->get();
    }

    private function applyAdditionalFilters(Collection $customers, array $filters, string $traceId): Collection
    {
        return $customers
            ->when($filters['search_address'] === 'NULL', fn($c) => $c->whereNull('address'))
            ->when($filters['search_address'] ?? null, fn($c, $addr) => $c->where('address', $addr))
            ->when($filters['server_search'] ?? null, fn($c, $server) => $c->where('mikrotik_id', $server))
            ->when($filters['search_package'] ?? null, fn($c, $package) => $c->where('package_id', $package))
            ->where('activation', 'true')
            ->sortBy('name')
            ->values();
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
            $this->testConnectionService->checkOnline($router, $traceId);

            // Dispatch job async
            DisablePppSecretJob::dispatch(
                $router,
                $customer->username,
                $customer->id
            )->onQueue('ppp');

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
        $customer->update(['disabled' => 'true']);
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
            // Check if router is online
            $this->testConnectionService->checkOnline($router, $traceId);

            // Dispatch async job
            EnablePppSecretJob::dispatch(
                $router,
                $customer->username,
                $customer->id
            )->onQueue('ppp');

            // Optimistic update (optional)
            $customer->update(['disabled' => 'false']);

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
        $customer->update(['disabled' => 'false']);
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
