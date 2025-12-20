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
        try {
            // Cek router
            $router = Mikrotik::find($customer->mikrotik_id);

            if (!$router) {
                return $this->offlineData(
                    null,
                    $customer,
                    "Router not found",
                    $traceId
                );
            }

            // Get PPP secret data
            $secretData = $this->getSecretData($router, $customer, $traceId);

            // Update customer IP & MAC jika ada
            $this->updateCustomerIpMac($customer, $secretData);

            return $this->onlineData($customer, $router, $secretData);
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'customer_id' => $customer->id,
                'router_id'   => $customer->mikrotik_id,
                'message'     => $e->getMessage()
            ], 500, 'Failed to get server data', $traceId);

            return $this->offlineData(
                $customer->mikrotik,
                $customer,
                $e->getMessage(),
                $traceId
            );
        }
    }

    /**
     * ============================
     *   GET PPP SECRET DATA
     * ============================
     */
    private function getSecretData($router, Customer $customer, string $traceId): array
    {
        try {
            $pppsecret = SecretResource::collection(
                $this->secretService->getSecretById($router, $customer->secret_id, $traceId)
            )->first();

            $secretActive = SecretResource::collection(
                $this->secretService->getOnePppSecretActive($router, $customer->username, $traceId)
            )->first();

            LogHelper::logInfo(null, [
                'customer_id'   => $customer->id,
                'secret_found'  => !is_null($pppsecret),
                'active_found'  => !is_null($secretActive),
            ], 200, 'PPP secrets retrieved', $traceId);

            return [
                'serverDisconnect' => 'false',
                'info_msg'         => "Server {$router->name} online",
                'pppsecret'        => $this->normalizeResource($pppsecret),
                'secretActive'     => $this->normalizeResource($secretActive),
            ];
        } catch (\Exception $e) {

            LogHelper::logError(null, [
                'customer_id' => $customer->id,
                'router'      => $router->name,
                'message'     => $e->getMessage()
            ], 500, 'Failed to fetch PPP secret', $traceId);

            return [
                'serverDisconnect' => 'true',
                'info_msg'         => "Failed to fetch PPP secret from {$router->name}",
                'pppsecret'        => null,
                'secretActive'     => null
            ];
        }
    }

    /**
     * ============================
     *   UPDATE IP + MAC CUSTOMER
     * ============================
     */
    private function updateCustomerIpMac(Customer $customer, array $secretData): void
    {
        $ip  = $secretData['secretActive']['address'] ?? null;
        $mac = $secretData['pppsecret']['last-caller-id'] ?? null;

        $changed = false;

        if ($ip) {
            $customer->ip_address = $ip;
            $changed = true;
        }

        if ($mac) {
            $customer->mac_address = $mac;
            $changed = true;
        }

        if ($changed) {
            $customer->save();
        }
    }

    /**
     * ============================
     *   RESPONSE: ONLINE
     * ============================
     */
    private function onlineData(Customer $customer, $router, array $secretData): array
    {
        return [
            'serverDisconnect' => $secretData['serverDisconnect'] ?? 'false',
            'info_msg'         => $secretData['info_msg'] ?? null,
            'customer'         => $customer,
            'mikrotik'         => $router,
            'ont'              => CustomerOnt::where('customer_id', $customer->id)->first(),
            'pppsecret'        => $secretData['pppsecret'] ?? [],
            'secretActive'     => $secretData['secretActive'] ?? [],
        ];
    }

    /**
     * ============================
     *   RESPONSE: OFFLINE
     * ============================
     */
    private function offlineData(?Mikrotik $router, Customer $customer, string $msg, string $traceId): array
    {
        return [
            'serverDisconnect' => 'true',
            'info_msg'         => $router ? "Server {$router->name} offline. ($msg)" : "Router unavailable. ($msg)",
            'customer'         => $customer,
            'mikrotik'         => $router,
            'ont'              => CustomerOnt::where('customer_id', $customer->id)->first(),
            'pppsecret'        => [],
            'secretActive'     => [],
        ];
    }

    /**
     * ============================
     *   HELPER: NORMALIZE RESOURCE
     * ============================
     */
    private function normalizeResource($resource): array
    {
        if (is_null($resource)) {
            return [];
        }

        if (is_array($resource)) {
            return $resource;
        }

        if ($resource instanceof \JsonSerializable) {
            return $resource->jsonSerialize();
        }

        return (array) $resource;
    }
}
