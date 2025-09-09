<?php

namespace App\Services\Customers;

use App\Helpers\LogHelper;
use App\Models\PppType;
use App\Models\Customer;
use App\Models\Mikrotik;
use App\Http\Resources\InterfaceResource;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\Ppp\SecretService;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;

class CustomerPppService
{
    public function __construct(
        private SecretService $secretService,
        private IpStaticService $ipStaticService
    ) {}

    public function getPppEditData(Customer $customer, string $traceId): array
    {
        try {
            $data = $customer->internet_service === 'ppp'
                ? $this->getPppData($customer, $traceId)
                : $this->getIpStaticData($customer, $traceId);

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'service_type' => $customer->internet_service,
                    'data_count' => count($data)
                ],
                200,
                'Successfully retrieved PPP edit data',
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
                'Failed to get PPP edit data',
                $traceId
            );
            throw $e;
        }
    }

    private function getPppData(Customer $customer, string $traceId): array
    {
        try {
            $services = PppType::orderBy('name', 'ASC')->pluck('name', 'id');

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'services_count' => count($services)
                ],
                200,
                'Successfully retrieved PPP data',
                $traceId
            );

            return [
                'services' => $services
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
                    'customer_id' => $customer->id
                ],
                500,
                'Failed to get PPP data',
                $traceId
            );
            throw $e;
        }
    }

    private function getIpStaticData(Customer $customer, string $traceId): array
    {
        try {
            $interfaces = InterfaceResource::collection(
                $this->ipStaticService->getInterfaces($customer->mikrotik, $traceId)
            )->where('disabled', 'false')->pluck('name', 'name')->toArray();

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'interfaces_count' => count($interfaces)
                ],
                200,
                'Successfully retrieved IP static data',
                $traceId
            );

            return ['interfaces' => $interfaces];
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
                    'router_id' => $customer->mikrotik->id ?? null
                ],
                500,
                'Failed to get IP static data',
                $traceId
            );
            throw $e;
        }
    }

    public function updatePppData(Customer $customer, array $validatedData, string $traceId)
    {
        $router = Mikrotik::find($customer->mikrotik_id);

        try {
            if ($customer->internet_service === 'ppp') {
                $result = $this->updatePppConnection($router, $customer, $validatedData, $traceId);
                LogHelper::logInfo(
                    null,
                    [
                        'customer_id' => $customer->id,
                        'service_type' => 'ppp',
                        'username' => $validatedData['username']
                    ],
                    200,
                    'Successfully updated PPP connection',
                    $traceId
                );
                return $result;
            }

            $result = $this->updateIpStatic($router, $customer, $validatedData, $traceId);
            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'service_type' => 'ip_static',
                    'ip_address' => $validatedData['ip_static']
                ],
                200,
                'Successfully updated IP static connection',
                $traceId
            );
            return $result;
        } catch (ConnectException $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'router_id' => $router->id ?? null
                ],
                503,
                'Router offline',
                $traceId
            );
            throw new \RuntimeException('Router offline, please check your router connection');
        } catch (ClientException | BadCredentialsException $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'router_id' => $router->id ?? null
                ],
                401,
                'Router authentication failed',
                $traceId
            );
            throw new \RuntimeException('Router authentication failed');
        } catch (QueryException $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'router_id' => $router->id ?? null
                ],
                400,
                'Router command failed',
                $traceId
            );
            throw new \RuntimeException('Router command failed: ' . $e->getMessage());
        } catch (ConfigException $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'router_id' => $router->id ?? null
                ],
                500,
                'Router configuration error',
                $traceId
            );
            throw new \RuntimeException('Router configuration error');
        } catch (\RuntimeException $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id ?? null
                ],
                400,
                'Failed to update PPP data',
                $traceId
            );
            throw $e;
        }
    }

    private function updatePppConnection(Mikrotik $router, Customer $customer, array $data, string $traceId)
    {
        try {
            $serviceName = PppType::find($data['ppp_type_id'])->name;
            $saveWithLastCallerId = filter_var($data['save_with_last_caller_id'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $callerId = $saveWithLastCallerId ? ($data['last_caller_id'] ?? null) : ($data['caller_id'] ?? null);

            $response = $this->secretService->updateUserPasswordServiceSecret(
                $router,
                $data['username'],
                $data['password_ppp'],
                $serviceName,
                $customer->secret_id,
                $callerId,
                $traceId
            );

            if (isset($response['after']['message'])) {
                LogHelper::logError(
                    null,
                    [
                        'exception' => [
                            'message' => $response['after']['message'],
                            'code' => 400
                        ],
                        'username' => $data['username'],
                        'router_id' => $router->id ?? null
                    ],
                    400,
                    'Failed to update PPP connection',
                    $traceId
                );
                throw new \RuntimeException($response['after']['message']);
            }

            if ($response === 'error') {
                LogHelper::logError(
                    null,
                    [
                        'exception' => [
                            'message' => "User {$data['username']} not found on server",
                            'code' => 404
                        ],
                        'username' => $data['username'],
                        'router_id' => $router->id ?? null
                    ],
                    404,
                    'User not found on server',
                    $traceId
                );
                throw new \RuntimeException("User {$data['username']} not found on server");
            }

            $updateData = [
                'username' => $data['username'],
                'password_ppp' => $data['password_ppp'],
                'ppp_type_id' => $data['ppp_type_id'],
                'caller_id' => $callerId
            ];

            if (!$customer->update($updateData)) {
                LogHelper::logError(
                    null,
                    [
                        'exception' => [
                            'message' => 'Failed to update customer data',
                            'code' => 500
                        ],
                        'customer_id' => $customer->id,
                        'update_data' => $updateData
                    ],
                    500,
                    'Failed to update customer data',
                    $traceId
                );
                throw new \RuntimeException('Failed to update customer data');
            }

            if (empty($data['dont_disconnect_client_button'])) {
                $this->secretService->deleteActiveSecret($router, $data['username'], $traceId);
            }

            // ✅ Log success
            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'router_id'   => $router->id ?? null,
                    'username'    => $data['username'],
                    'ppp_type_id' => $data['ppp_type_id'],
                    'caller_id'   => $callerId
                ],
                200,
                'Successfully updated PPP connection',
                $traceId
            );

            // ✅ Return success response
            return [
                'status'  => true,
                'code'    => 200,
                'message' => 'Successfully updated PPP connection',
                'data'    => $updateData
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
                    'customer_id' => $customer->id ?? null,
                    'router_id' => $router->id ?? null
                ],
                500,
                'Failed to update PPP connection',
                $traceId
            );
            throw $e;
        }
    }

    private function updateIpStatic(Mikrotik $router, Customer $customer, array $data, string $traceId)
    {
        try {
            $arpResponse = $this->ipStaticService->setIpAddressAndMacAddress(
                $router,
                $customer->arp_id,
                $data['ip_static'],
                $data['mac_address'],
                $data['interface'],
                $traceId
            );

            $queueResponse = $this->ipStaticService->setIpSimpleque(
                $router,
                $customer->simpleque_id,
                $data['ip_static'],
                $traceId
            );

            if (isset($arpResponse['after']['message'])) {
                LogHelper::logError(
                    null,
                    [
                        'exception' => [
                            'message' => $arpResponse['after']['message'],
                            'code' => 400
                        ],
                        'ip_address' => $data['ip_static'],
                        'router_id' => $router->id ?? null
                    ],
                    400,
                    'ARP update failed',
                    $traceId
                );
                throw new \RuntimeException("ARP Error: " . $arpResponse['after']['message']);
            }

            if (isset($queueResponse['after']['message'])) {
                LogHelper::logError(
                    null,
                    [
                        'exception' => [
                            'message' => $queueResponse['after']['message'],
                            'code' => 400
                        ],
                        'ip_address' => $data['ip_static'],
                        'router_id' => $router->id ?? null
                    ],
                    400,
                    'Queue update failed',
                    $traceId
                );
                throw new \RuntimeException("Queue Error: " . $queueResponse['after']['message']);
            }

            $updateData = [
                'ip_static' => $data['ip_static'],
                'mac_address' => $data['mac_address'],
                'interface' => $data['interface']
            ];

            if (!$customer->update($updateData)) {
                LogHelper::logError(
                    null,
                    [
                        'exception' => [
                            'message' => 'Failed to update IP static data',
                            'code' => 500
                        ],
                        'customer_id' => $customer->id,
                        'update_data' => $updateData
                    ],
                    500,
                    'Failed to update IP static data',
                    $traceId
                );
                throw new \RuntimeException('Failed to update IP static data');
            }
        } catch (ConnectException $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'router_id' => $router->id ?? null
                ],
                503,
                'Cannot connect to router',
                $traceId
            );
            throw new \RuntimeException('Cannot connect to router while updating IP static');
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
                    'customer_id' => $customer->id ?? null,
                    'router_id' => $router->id ?? null
                ],
                500,
                'Failed to update IP static connection',
                $traceId
            );
            throw $e;
        }
    }
}
