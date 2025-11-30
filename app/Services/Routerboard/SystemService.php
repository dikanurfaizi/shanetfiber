<?php

namespace App\Services\Routerboard;

use App\Http\Resources\TrafficInterfaceResource;
use App\Models\Mikrotik;
use Exception;
use Illuminate\Http\JsonResponse;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;
use App\Helpers\LogHelper;
use App\Helpers\SnmpHelper;

class SystemService
{
    public function getAllResources($router, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($router, $traceId);

            if (!$client) {
                LogHelper::logWarning(
                    null,
                    ['router' => $router->host ?? 'unknown'],
                    503,
                    'Mikrotik client is null — connection failed',
                    $traceId
                );
                return []; // atau lempar exception baru
            }

            $query = new Query('/system/resource/print');
            $resources = $client->query($query)->read();

            if (empty($resources)) {
                LogHelper::logWarning(
                    null,
                    [],
                    404,
                    "No system resources found",
                    $traceId
                );
                return [];
            }

            return $resources;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get system resources",
                $traceId
            );
            throw $e;
        }
    }

    public function getTime($router, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($router, $traceId);

            if (!$client) {
                LogHelper::logWarning(
                    null,
                    ['router' => $router->host ?? 'unknown'],
                    503,
                    'Mikrotik client is null — connection failed',
                    $traceId
                );
                return []; // atau lempar exception baru
            }

            $query = new Query('/system/clock/print');
            $time = $client->query($query)->read();

            if (empty($time)) {
                LogHelper::logWarning(
                    null,
                    [],
                    404,
                    "System time not found",
                    $traceId
                );
                return [];
            }

            return $time;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get system time",
                $traceId
            );
            throw $e;
        }
    }

    public function monitoringTraffic(Mikrotik $routerboard, string $traceId): JsonResponse
    {
        try {
            $interface = request()->interface;
            $client = MikrotikService::getMikrotik($routerboard, $traceId);

            if (!$client) {
                LogHelper::logWarning(
                    null,
                    ['router' => $router->host ?? 'unknown'],
                    503,
                    'Mikrotik client is null — connection failed',
                    $traceId
                );
                return response()->json([], 503); // atau lempar exception baru
            }

            $eth = (new Query('/interface/monitor-traffic'))
                ->equal('interface', $interface)
                ->equal('once');
            $tf = $client->query($eth)->read();

            if (empty($tf)) {
                LogHelper::logWarning(
                    null,
                    ['interface' => $interface],
                    404,
                    "No traffic data found for interface",
                    $traceId
                );
                return response()->json([]);
            }

            $traffic = TrafficInterfaceResource::collection($tf);

            LogHelper::logInfo(
                null,
                [
                    'interface' => $interface,
                    'data_count' => count($tf)
                ],
                200,
                "Traffic monitoring data retrieved successfully",
                $traceId
            );

            return response()->json($traffic);
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'interface' => $interface ?? 'N/A',
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to monitor traffic",
                $traceId
            );
            throw $e;
        }
    }

    public function mikrotikInterface(Mikrotik $routerboard, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($routerboard, $traceId);

            if (!$client) {
                LogHelper::logError(
                    null,
                    ['mikrotik_id' => $routerboard->id ?? null],
                    500,
                    "Failed to connect to Mikrotik",
                    $traceId
                );
                return []; // return kosong agar job tetap berjalan
            }

            $inf = new Query('/interface/print');
            $interfaces = $client->query($inf)->read();

            if (empty($interfaces)) {
                LogHelper::logWarning(
                    null,
                    [],
                    404,
                    "No interfaces found",
                    $traceId
                );
                return [];
            }

            LogHelper::logInfo(
                null,
                [
                    'interface_count' => count($interfaces)
                ],
                200,
                "Interfaces retrieved successfully",
                $traceId
            );

            return $interfaces;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'error' => $e->getMessage(),
                    'mikrotik_id' => $routerboard->id ?? null
                ],
                500,
                "Failed to get interfaces",
                $traceId
            );
            return []; // jangan throw, cukup return kosong
        }
    }

    public function mikrotikEtherInterface(Mikrotik $routerboard, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($routerboard, $traceId);

            if (!$client) {
                LogHelper::logWarning(
                    null,
                    ['router' => $router->host ?? 'unknown'],
                    503,
                    'Mikrotik client is null — connection failed',
                    $traceId
                );
                return []; // atau lempar exception baru
            }

            $query = new Query('/interface/print');
            $query->where('type', 'ether');
            $query->where('type', 'vlan');
            $query->operations('|');
            $interfaces = $client->query($query)->read();

            if (empty($interfaces)) {
                LogHelper::logWarning(
                    null,
                    [],
                    404,
                    "No ethernet/vlan interfaces found",
                    $traceId
                );
                return [];
            }

            LogHelper::logInfo(
                null,
                [
                    'interface_count' => count($interfaces)
                ],
                200,
                "Ethernet/VLAN interfaces retrieved successfully",
                $traceId
            );

            return $interfaces;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get ethernet/vlan interfaces",
                $traceId
            );
            throw $e;
        }
    }

    public function getInterfacePppByName($router, $username, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($router, $traceId);

            if (!$client) {
                LogHelper::logWarning(
                    null,
                    ['router' => $router->host ?? 'unknown'],
                    503,
                    'Mikrotik client is null — connection failed',
                    $traceId
                );
                return []; // atau lempar exception baru
            }

            $inf = new Query('/interface/print');
            $inf->where('name', $username);
            $interface = $client->query($inf)->read();

            if (empty($interface)) {
                LogHelper::logWarning(
                    null,
                    ['username' => $username],
                    404,
                    "PPP interface not found",
                    $traceId
                );
                return [];
            }

            return $interface;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'username' => $username,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get PPP interface by name",
                $traceId
            );
            throw $e;
        }
    }
}
