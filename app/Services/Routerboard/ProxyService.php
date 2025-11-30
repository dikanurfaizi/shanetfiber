<?php

namespace App\Services\Routerboard;

use Exception;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;
use App\Helpers\LogHelper;

class ProxyService
{
    public function createAccessServiceV6_49($routerboard, $srcAddress, $natDstAddress, $action, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($routerboard, $traceId);

            $query = new Query('/ip/proxy/access/add');
            $query->equal('src-address', $srcAddress);
            $query->equal('action', $action);
            if ($action == 'deny') {
                $query->equal('redirect-to', $natDstAddress . '/isolir');
            } else if ($action == 'allow') {
                $query->equal('!redirect-to');
            }
            $response = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'src_address' => $srcAddress,
                    'nat_dst_address' => $natDstAddress,
                    'action' => $action,
                    'response' => $response
                ],
                200,
                "Proxy access service created successfully (v6.49)",
                $traceId
            );

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'src_address' => $srcAddress,
                    'nat_dst_address' => $natDstAddress,
                    'action' => $action,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to create proxy access service (v6.49)",
                $traceId
            );
            throw $e;
        }
    }

    public function createAccessServiceV7_10($routerboard, $srcAddress, $natDstAddress, $action, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($routerboard, $traceId);

            $query = new Query('/ip/proxy/access/add');
            $query->equal('src-address', $srcAddress);
            $query->equal('action', $action);
            if ($action == 'redirect') {
                $query->equal('action-data', $natDstAddress . '/isolir');
            }
            $response = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'src_address' => $srcAddress,
                    'nat_dst_address' => $natDstAddress,
                    'action' => $action,
                    'response' => $response
                ],
                200,
                "Proxy access service created successfully (v7.10)",
                $traceId
            );

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'src_address' => $srcAddress,
                    'nat_dst_address' => $natDstAddress,
                    'action' => $action,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to create proxy access service (v7.10)",
                $traceId
            );
            throw $e;
        }
    }

    public function updateAccessServiceV7_10($routerboard, $srcAddress, $natDstAddress, $id, $action, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($routerboard, $traceId);
            $accessService = new Query('/ip/proxy/access/print');
            $accessService->where('.id', $id);
            $results = $client->query($accessService)->read();

            if (empty($results)) {
                LogHelper::logWarning(
                    null,
                    ['id' => $id],
                    404,
                    "Proxy access service not found for update (v7.10)",
                    $traceId
                );
                return null;
            }

            foreach ($results as $result) {
                $query = new Query('/ip/proxy/access/set');
                $query->where('.id', $id);
                $query->equal('.id', $result['.id']);
                $query->equal('src-address', $srcAddress);
                $query->equal('action', $action);
                if ($action == 'redirect') {
                    $query->equal('action-data', $natDstAddress . '/isolir');
                }
                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'id' => $id,
                        'src_address' => $srcAddress,
                        'nat_dst_address' => $natDstAddress,
                        'action' => $action,
                        'response' => $response
                    ],
                    200,
                    "Proxy access service updated successfully (v7.10)",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'id' => $id,
                    'src_address' => $srcAddress,
                    'nat_dst_address' => $natDstAddress,
                    'action' => $action,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to update proxy access service (v7.10)",
                $traceId
            );
            throw $e;
        }
    }

    public function updateAccessServiceV6_49($routerboard, $srcAddress, $natDstAddress, $id, $action, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($routerboard, $traceId);
            $accessService = new Query('/ip/proxy/access/print');
            $accessService->where('.id', $id);
            $results = $client->query($accessService)->read();

            if (empty($results)) {
                LogHelper::logWarning(
                    null,
                    ['id' => $id],
                    404,
                    "Proxy access service not found for update (v6.49)",
                    $traceId
                );
                return null;
            }

            foreach ($results as $result) {
                $query = new Query('/ip/proxy/access/set');
                $query->where('.id', $id);
                $query->equal('.id', $result['.id']);
                $query->equal('src-address', $srcAddress);
                $query->equal('action', $action);
                if ($action == 'deny') {
                    $query->equal('redirect-to', $natDstAddress . '/isolir');
                } else if ($action == 'allow') {
                    $query->equal('!redirect-to');
                }
                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'id' => $id,
                        'src_address' => $srcAddress,
                        'nat_dst_address' => $natDstAddress,
                        'action' => $action,
                        'response' => $response
                    ],
                    200,
                    "Proxy access service updated successfully (v6.49)",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'id' => $id,
                    'src_address' => $srcAddress,
                    'nat_dst_address' => $natDstAddress,
                    'action' => $action,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to update proxy access service (v6.49)",
                $traceId
            );
            throw $e;
        }
    }

    public function removeAccessService($routerboard, $id, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($routerboard, $traceId);
            $accessService = new Query('/ip/proxy/access/print');
            $accessService->where('.id', $id);
            $results = $client->query($accessService)->read();

            if (empty($results)) {
                LogHelper::logWarning(
                    null,
                    ['id' => $id],
                    404,
                    "Proxy access service not found for removal",
                    $traceId
                );
                return null;
            }

            foreach ($results as $result) {
                $query = (new Query('/ip/proxy/access/remove'))
                    ->where('.id', $id)
                    ->equal('.id', $result['.id']);
                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'id' => $id,
                        'response' => $response
                    ],
                    200,
                    "Proxy access service removed successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'id' => $id,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to remove proxy access service",
                $traceId
            );
            throw $e;
        }
    }

    public function getAccessService($routerboard, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($routerboard, $traceId);
            $accessService = new Query('/ip/proxy/access/print');
            $response = $client->query($accessService)->read();

            if (empty($response)) {
                LogHelper::logWarning(
                    null,
                    [],
                    404,
                    "No proxy access services found",
                    $traceId
                );
                return [];
            }

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get proxy access services",
                $traceId
            );
            throw $e;
        }
    }

    public function enableProxyService($routerboard, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($routerboard, $traceId);
            $query = (new query('/ip/proxy/set'))
                ->equal('enabled', 'true')
                ->equal('port', '8080');
            $response = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'response' => $response
                ],
                200,
                "Proxy service enabled successfully",
                $traceId
            );

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to enable proxy service",
                $traceId
            );
            throw $e;
        }
    }

    public function disableProxyService($routerboard, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($routerboard, $traceId);
            $query = (new query('/ip/proxy/set'))
                ->equal('enabled', 'false');
            $response = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'response' => $response
                ],
                200,
                "Proxy service disabled successfully",
                $traceId
            );

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to disable proxy service",
                $traceId
            );
            throw $e;
        }
    }
}
