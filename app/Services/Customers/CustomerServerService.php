<?php

namespace App\Services\Customers;

use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Models\Customers\CustomerOnt;
use App\Services\Routerboard\Ppp\SecretService;
use App\Http\Resources\Pppoe\SecretResource;
use App\Helpers\LogHelper;

class CustomerServerService
{
    public function __construct(
        private SecretService $secretService
    ) {}

    /**
     * SERVER TAB DATA ONLY
     */
    public function getServerData(Customer $customer, string $traceId): array
    {
        // LogHelper::logInfo(null, [
        //     'step'        => 'START_getServerData',
        //     'trace_id'   => $traceId,
        //     'customer_id' => $customer->id,
        //     'router_id'  => $customer->mikrotik_id,
        // ], 200, 'Server tab request received', $traceId);

        try {
            // ============================
            // STEP 1: ROUTER CHECK
            // ============================
            $router = Mikrotik::find($customer->mikrotik_id);

            // LogHelper::logInfo(null, [
            //     'step'        => 'CHECK_router',
            //     'router_found' => !is_null($router),
            //     'router_id'   => $customer->mikrotik_id,
            // ], 200, 'Router lookup result', $traceId);

            if (!$router) {
                return $this->offlineData(
                    null,
                    $customer,
                    'Router not found',
                    $traceId
                );
            }

            // ============================
            // STEP 2: PPP SECRET
            // ============================
            $secretData = $this->getSecretData($router, $customer, $traceId);

            // LogHelper::logInfo(null, [
            //     'step'              => 'PPP_SECRET_RESULT',
            //     'serverDisconnect'  => $secretData['serverDisconnect'],
            //     'secretActive_empty' => empty($secretData['secretActive']),
            //     'pppsecret_empty'   => empty($secretData['pppsecret']),
            // ], 200, 'PPP secret data processed', $traceId);

            // ============================
            // STEP 3: UPDATE IP & MAC
            // ============================
            $this->updateCustomerIpMac($customer, $secretData);

            // ============================
            // STEP 4: FINAL RESPONSE
            // ============================
            $response = $this->onlineData($customer, $router, $secretData);

            LogHelper::logInfo(null, [
                'step'        => 'RESPONSE_online',
                'serverDisconnect' => $response['serverDisconnect'],
                'has_ont'     => !is_null($response['ont']),
            ], 200, 'Server tab online response ready', $traceId);

            return $response;
        } catch (\Throwable $e) {

            LogHelper::logError(null, [
                'step'        => 'EXCEPTION_getServerData',
                'customer_id' => $customer->id,
                'router_id'  => $customer->mikrotik_id,
                'message'    => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ], 500, 'Unhandled exception in server tab', $traceId);

            return $this->offlineData(
                $customer->mikrotik,
                $customer,
                $e->getMessage(),
                $traceId
            );
        }
    }

    /**
     * GET PPP SECRET DATA
     */
    private function getSecretData($router, Customer $customer, string $traceId): array
    {
        // LogHelper::logInfo(null, [
        //     'step'        => 'START_getSecretData',
        //     'router'      => $router->name,
        //     'customer_id' => $customer->id,
        // ], 200, 'Fetching PPP secret', $traceId);

        try {
            $pppsecret = SecretResource::collection(
                $this->secretService->getSecretById(
                    $router,
                    $customer->secret_id,
                    $traceId
                )
            )->first();

            $secretActive = SecretResource::collection(
                $this->secretService->getOnePppSecretActive(
                    $router,
                    $customer->username,
                    $traceId
                )
            )->first();

            LogHelper::logInfo(null, [
                'step'          => 'PPP_SECRET_FETCHED',
                'ppp_found'     => !is_null($pppsecret),
                'active_found'  => !is_null($secretActive),
            ], 200, 'PPP secret fetch success', $traceId);

            return [
                'serverDisconnect' => 'false',
                'info_msg'         => "Server {$router->name} online",
                'pppsecret'        => $this->normalizeResource($pppsecret),
                'secretActive'     => $this->normalizeResource($secretActive),
                'uptime'            => $secretActive['uptime']
            ];
        } catch (\Throwable $e) {

            LogHelper::logError(null, [
                'step'      => 'PPP_SECRET_ERROR',
                'router'    => $router->name,
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ], 500, 'PPP secret fetch failed', $traceId);

            return [
                'serverDisconnect' => 'true',
                'info_msg'         => "Failed to fetch PPP secret from {$router->name}",
                'pppsecret'        => [],
                'secretActive'     => [],
            ];
        }
    }

    /**
     * UPDATE IP & MAC
     */
    private function updateCustomerIpMac(Customer $customer, array $secretData): void
    {
        $ip     = $secretData['secretActive']['address'] ?? null;
        $mac    = $secretData['pppsecret']['last-caller-id'] ?? null;
        $uptime = $this->parseUptime(
            $secretData['uptime'] ?? null
        );

        // LogHelper::logInfo(null, [
        //     'step'        => 'UPDATE_IP_MAC_CHECK',
        //     'has_ip'      => !empty($ip),
        //     'has_mac'     => !empty($mac),
        //     'has_uptime'  => !is_null($uptime),
        // ], 200, 'Checking IP/MAC/uptime update', null);

        if ($ip || $mac || $uptime !== null) {
            $customer->ip_address     = $ip ?? $customer->ip_address;
            $customer->mac_address    = $mac ?? $customer->mac_address;
            $customer->caller_id      = $mac ?? $customer->caller_id;
            $customer->uptime_seconds = $uptime ?? $customer->uptime_seconds;

            $customer->save();

            LogHelper::logInfo(null, [
                'step' => 'UPDATE_IP_MAC_SAVED'
            ], 200, 'Customer IP/MAC/uptime updated', null);
        }
    }

    private function parseUptime(?string $raw): ?int
    {
        if (!$raw) {
            return null;
        }

        preg_match_all('/(\d+)([dhms])/', $raw, $m, PREG_SET_ORDER);

        $seconds = 0;

        foreach ($m as [, $val, $unit]) {
            $seconds += match ($unit) {
                'd' => $val * 86400,
                'h' => $val * 3600,
                'm' => $val * 60,
                's' => $val,
                default => 0,
            };
        }

        return $seconds ?: null;
    }

    /**
     * RESPONSE ONLINE
     */
    private function onlineData(Customer $customer, $router, array $secretData): array
    {
        return [
            'serverDisconnect' => $secretData['serverDisconnect'],
            'info_msg'         => $secretData['info_msg'],
            'customer'         => $customer,
            'mikrotik'         => $router,
            'ont'              => CustomerOnt::where('customer_id', $customer->id)->first(),
            'pppsecret'        => $secretData['pppsecret'],
            'secretActive'     => $secretData['secretActive'],
        ];
    }

    /**
     * RESPONSE OFFLINE
     */
    private function offlineData(?Mikrotik $router, Customer $customer, string $msg, string $traceId): array
    {
        // LogHelper::logInfo(null, [
        //     'step' => 'RESPONSE_offline',
        //     'reason' => $msg,
        // ], 200, 'Server tab offline response', $traceId);

        return [
            'serverDisconnect' => 'true',
            'info_msg'         => $router
                ? "Server {$router->name} offline. ($msg)"
                : "Router unavailable. ($msg)",
            'customer'         => $customer,
            'mikrotik'         => $router,
            'ont'              => CustomerOnt::where('customer_id', $customer->id)->first(),
            'pppsecret'        => [],
            'secretActive'     => [],
        ];
    }

    /**
     * NORMALIZE RESOURCE
     */
    private function normalizeResource($resource): array
    {
        if (is_null($resource)) return [];

        if (is_array($resource)) return $resource;

        if ($resource instanceof \JsonSerializable) {
            return $resource->jsonSerialize();
        }

        return (array) $resource;
    }
}
