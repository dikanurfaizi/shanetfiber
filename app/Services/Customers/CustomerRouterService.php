<?php

namespace App\Services\Customers;

use App\Models\MerkRouter;
use App\Models\TypeRouter;
use App\Models\Customer;
use App\Models\CustomerOnt;
use Illuminate\Support\Collection;
use App\Helpers\LogHelper;

class CustomerRouterService
{
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

    private function getTypeRouters($customerRouter, string $traceId): Collection
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
}
