<?php

namespace App\Services\Customers;

use App\Models\MerkRouter;
use App\Models\TypeRouter;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerOnt;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use Illuminate\Support\Collection;
use App\Helpers\LogHelper;
use App\Jobs\Secrets\DeleteIpStaticJob;
use App\Services\Routerboard\TestConnectionService;
use App\Services\Routerboard\Ppp\SecretService;

class CustomerRouterService
{
    private TestConnectionService $testConnectionService;
    private SecretService $secretService;

    public function __construct(
        TestConnectionService $testConnectionService,
        SecretService $secretService,
    ) {
        $this->testConnectionService = $testConnectionService;
        $this->secretService = $secretService;
    }

    public function getRouterEditData(Customer $customer, string $traceId): array
    {
        try {
            $customerRouter = CustomerOnt::where('customer_id', $customer->id)->first();

            $data = [
                'merkRouters' => MerkRouter::where('disabled', 'false')->orderBy('name', 'ASC')->get(),
                'typeRouters' => $this->getTypeRouters($customerRouter, $traceId),
                'customerRouter' => $customerRouter
            ];

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'merk_routers_count' => $data['merkRouters']->count(),
                    'type_routers_count' => $data['typeRouters']->count()
                ],
                200,
                'Successfully fetched router edit data',
                $traceId
            );

            return $data;
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
                    'customer_id' => $customer->id
                ],
                500,
                'Failed to fetch router edit data',
                $traceId
            );
            throw $e;
        }
    }

    public function getTypeRouters($customerRouter, string $traceId): Collection
    {
        try {
            if (!$customerRouter?->merk_ont) {
                LogHelper::logWarning(
                    null,
                    [
                        'customer_router_id' => $customerRouter->id ?? null
                    ],
                    404,
                    'Merk ONT not found for customer router',
                    $traceId
                );
                return collect();
            }

            $merkId = MerkRouter::where('name', $customerRouter->merk_ont)
                ->value('id');

            $typeRouters = TypeRouter::where('merk_router_id', $merkId)
                ->where('disabled', 'false')
                ->orderBy('name', 'ASC')
                ->get();

            LogHelper::logInfo(
                null,
                [
                    'merk_id' => $merkId,
                    'type_routers_count' => $typeRouters->count()
                ],
                200,
                'Successfully fetched type routers',
                $traceId
            );

            return $typeRouters;
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
                    'customer_router_id' => $customerRouter->id ?? null
                ],
                500,
                'Failed to fetch type routers',
                $traceId
            );
            return collect();
        }
    }

    public function updateRouterData(Customer $customer, array $validatedData, string $traceId): bool
    {
        try {
            $result = (bool) CustomerOnt::updateOrCreate(
                ['customer_id' => $customer->id],
                [
                    'merk_ont' => $validatedData['merk_ont'],
                    'type_ont' => $validatedData['type_ont'] ?? null,
                    'username' => $validatedData['username'] ?? null,
                    'password' => $validatedData['password'] ?? null,
                    'port' => $validatedData['port'] ?? null,
                    'mac_address' => $validatedData['mac_address'] ?? null,
                    'description' => $validatedData['description'] ?? null
                ]
            );

            if ($result) {
                LogHelper::logInfo(
                    null,
                    [
                        'customer_id' => $customer->id,
                        'merk_ont' => $validatedData['merk_ont'],
                        'type_ont' => $validatedData['type_ont'] ?? null
                    ],
                    200,
                    'Successfully updated router data',
                    $traceId
                );
            } else {
                LogHelper::logError(
                    null,
                    [
                        'customer_id' => $customer->id,
                        'update_data' => $validatedData
                    ],
                    500,
                    'Failed to update router data (no exception)',
                    $traceId
                );
            }

            return $result;
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
                    'customer_id' => $customer->id,
                    'update_data' => $validatedData
                ],
                500,
                'Failed to update router data',
                $traceId
            );
            throw new \RuntimeException('Failed to update router data: ' . $e->getMessage());
        }
    }

    public function deactivateCustomerServices(Customer $customer, string $traceId): void
    {
        try {
            $router = Mikrotik::find($customer->mikrotik_id);
            $autoisolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            if (!$router) {
                LogHelper::logError(
                    null,
                    [
                        'exception' => [
                            'message' => 'Router not found',
                            'code' => 404,
                            'file' => __FILE__,
                            'line' => __LINE__
                        ],
                        'customer_id' => $customer->id,
                        'mikrotik_id' => $customer->mikrotik_id
                    ],
                    404,
                    'Router not found for customer',
                    $traceId
                );
                throw new \RuntimeException('Router not found for customer ID: ' . $customer->id);
            }

            $this->handleInternetService($customer, $router, $autoisolir, $traceId);

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'router_id' => $router->id,
                    'autoisolir_id' => $autoisolir->id ?? null
                ],
                200,
                'Successfully deactivated customer services',
                $traceId
            );
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
                    'customer_id' => $customer->id,
                    'mikrotik_id' => $customer->mikrotik_id ?? null
                ],
                500,
                'Failed to deactivate customer services',
                $traceId
            );
            throw $e;
        }
    }

    private function handleInternetService(Customer $customer, $router, $autoisolir, string $traceId)
    {
        if (!$router) {
            LogHelper::logError(
                null,
                ['customer_id' => $customer->id],
                404,
                'Router not found for customer',
                $traceId
            );
            throw new \Exception('Router not found for customer ID: ' . $customer->id);
        }

        try {
            $this->testConnectionService->checkOnline($router, $traceId);
            LogHelper::logInfo(
                null,
                ['customer_id' => $customer->id],
                200,
                "Connection to router succeeded",
                $traceId
            );

            if ($customer->internet_service === 'ppp') {
                $this->handlePppService($router, $customer->username, $traceId);
            } elseif ($customer->internet_service === 'ip_static') {
                $this->handleIpStaticService($router, $customer, $autoisolir, $traceId);
            }
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
                    'customer_id' => $customer->id,
                    'router_id' => $router->id ?? null
                ],
                500,
                'Failed to handle internet service',
                $traceId
            );
            throw $e;
        }
    }

    private function handlePppService($router, $username, string $traceId)
    {
        try {
            $this->secretService->deleteSecretByName($router, $username, $traceId);
            $this->secretService->deleteActiveSecret($router, $username, $traceId);
            LogHelper::logInfo(
                null,
                ['username' => $username],
                200,
                "PPP service deleted successfully",
                $traceId
            );
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
                    'username' => $username,
                    'router_id' => $router->id ?? null
                ],
                500,
                'Failed to delete PPP service',
                $traceId
            );
            throw $e;
        }
    }

    private function handleIpStaticService($router, Customer $customer, $autoisolir, string $traceId)
    {
        try {
            $simpleque_id = $customer->simpleque_id;
            $ip_address = $customer->ip_static;
            $arp_id = $customer->arp_id;
            $address_list_name = $autoisolir->address_list_isolir ?? 'none';

            dispatch(new DeleteIpStaticJob($router, $simpleque_id, $arp_id, $ip_address, $address_list_name, $traceId))->onQueue('default');
            LogHelper::logInfo(
                null,
                ['customer_id' => $customer->id],
                200,
                "IP static service deletion queued",
                $traceId
            );
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
                    'customer_id' => $customer->id,
                    'router_id' => $router->id ?? null
                ],
                500,
                'Failed to queue IP static service deletion',
                $traceId
            );
            throw $e;
        }
    }
}
