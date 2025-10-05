<?php

namespace App\Services\Routerboard;

use App\Helpers\LogHelper;
use Exception;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;

class FirewallService
{
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function getIpAddressList($routerboard, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = (new Query('/ip/firewall/address-list/print'))
                ->where('disabled', 'false');
            $result = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'router_id' => $routerboard->id,
                    'address_list_count' => count($result)
                ],
                200,
                "Successfully retrieved IP address list",
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
                    'router_id' => $routerboard->id ?? null
                ],
                500,
                "Failed to retrieve IP address list",
                $traceId
            );
            throw $e;
        }
    }

    public function createNatService($routerboard, $dstAddress, $srcAddressList, $dstAddressList, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = new Query('/ip/firewall/nat/add');
            $query->equal('chain', 'dstnat');
            $query->equal('action', 'redirect');
            $query->equal('to-ports', '8080');
            $query->equal('protocol', 'tcp');
            $query->equal('dst-address', '!' . $dstAddress);

            if ($srcAddressList) {
                $query->equal('src-address-list', $srcAddressList);
            } else {
                $query->equal('!src-address-list');
            }

            if ($dstAddressList) {
                $query->equal('dst-address-list', '!' . $dstAddressList);
            } else {
                $query->equal('!dst-address-list');
            }

            $query->equal('dst-port', '80,443');
            $query->equal('comment', 'Create from customer management to redirect isolir profile');
            $result = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'router_id' => $routerboard->id,
                    'dst_address' => $dstAddress,
                    'src_address_list' => $srcAddressList,
                    'dst_address_list' => $dstAddressList
                ],
                201,
                "Successfully created NAT service",
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
                    'router_id' => $routerboard->id ?? null,
                    'dst_address' => $dstAddress ?? null
                ],
                500,
                "Failed to create NAT service",
                $traceId
            );
            throw $e;
        }
    }

    public function updateNatService($routerboard, $dstAddress, $srcAddressList, $dstAddressList, $id, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $natService = new Query('/ip/firewall/nat/print');
            $natService->where('.id', $id);
            $results = $client->query($natService)->read();

            foreach ($results as $result) {
                $client = MikrotikConnectionService::getClient($routerboard, $traceId);
                $query = new Query('/ip/firewall/nat/set');
                $query->where('.id', $id);
                $query->equal('.id', $result['.id']);
                $query->equal('dst-address', '!' . $dstAddress);

                if ($srcAddressList) {
                    $query->equal('src-address-list', $srcAddressList);
                } else {
                    $query->equal('!src-address-list');
                }
                if ($dstAddressList) {
                    $query->equal('dst-address-list', '!' . $dstAddressList);
                } else {
                    $query->equal('!dst-address-list');
                }

                $result = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'router_id' => $routerboard->id,
                        'nat_id' => $id,
                        'new_dst_address' => $dstAddress
                    ],
                    200,
                    "Successfully updated NAT service",
                    $traceId
                );

                return $result;
            }
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
                    'router_id' => $routerboard->id ?? null,
                    'nat_id' => $id ?? null
                ],
                500,
                "Failed to update NAT service",
                $traceId
            );
            throw $e;
        }
    }

    public function createFilterDropService($routerboard, $srcAddressList, $dstAddress, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = new Query('/ip/firewall/filter/add');
            $query->equal('protocol', 'tcp');
            $query->equal('chain', 'forward');
            $query->equal('dst-address', '!' . $dstAddress);
            $query->equal('src-address-list', $srcAddressList);
            $query->equal('action', 'drop');
            $query->equal('comment', 'Create from customer management to drop internet');
            $result = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'router_id' => $routerboard->id,
                    'src_address_list' => $srcAddressList,
                    'dst_address' => $dstAddress
                ],
                201,
                "Successfully created filter drop service",
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
                    'router_id' => $routerboard->id ?? null,
                    'src_address_list' => $srcAddressList ?? null
                ],
                500,
                "Failed to create filter drop service",
                $traceId
            );
            throw $e;
        }
    }

    public function updateFilterDropService($routerboard, $srcAddressList, $dstAddress, $id, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $filterService = new Query('/ip/firewall/filter/print');
            $filterService->where('.id', $id);
            $results = $client->query($filterService)->read();

            foreach ($results as $result) {
                $client = MikrotikConnectionService::getClient($routerboard, $traceId);
                $query = new Query('/ip/firewall/filter/set');
                $query->where('.id', $id);
                $query->equal('.id', $result['.id']);
                $query->equal('dst-address', '!' . $dstAddress);
                $query->equal('src-address-list', $srcAddressList);
                $result = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'router_id' => $routerboard->id,
                        'filter_id' => $id,
                        'new_src_address_list' => $srcAddressList
                    ],
                    200,
                    "Successfully updated filter drop service",
                    $traceId
                );

                return $result;
            }
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
                    'router_id' => $routerboard->id ?? null,
                    'filter_id' => $id ?? null
                ],
                500,
                "Failed to update filter drop service",
                $traceId
            );
            throw $e;
        }
    }

    public function getNatService($routerboard, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = (new Query('/ip/firewall/nat/print'));
            $result = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'router_id' => $routerboard->id,
                    'nat_entries_count' => count($result)
                ],
                200,
                "Successfully retrieved NAT services",
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
                    'router_id' => $routerboard->id ?? null
                ],
                500,
                "Failed to retrieve NAT services",
                $traceId
            );
            throw $e;
        }
    }

    public function removeNatService($routerboard, $id, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $natService = new Query('/ip/firewall/nat/print');
            $natService->where('.id', $id);
            $results = $client->query($natService)->read();

            foreach ($results as $result) {
                $client = MikrotikConnectionService::getClient($routerboard, $traceId);
                $query = (new Query('/ip/firewall/nat/remove'))
                    ->where('.id', $id)
                    ->equal('.id', $result['.id']);

                $result = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'router_id' => $routerboard->id,
                        'nat_id' => $id
                    ],
                    200,
                    "Successfully removed NAT service",
                    $traceId
                );

                return $result;
            }
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
                    'router_id' => $routerboard->id ?? null,
                    'nat_id' => $id ?? null
                ],
                500,
                "Failed to remove NAT service",
                $traceId
            );
            throw $e;
        }
    }

    public function removeFilterDropService($routerboard, $id, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $filterService = new Query('/ip/firewall/filter/print');
            $filterService->where('.id', $id);
            $results = $client->query($filterService)->read();

            foreach ($results as $result) {
                $client = MikrotikConnectionService::getClient($routerboard, $traceId);
                $query = (new Query('/ip/firewall/filter/remove'))
                    ->where('.id', $id)
                    ->equal('.id', $result['.id']);

                $result = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'router_id' => $routerboard->id,
                        'filter_id' => $id
                    ],
                    200,
                    "Successfully removed filter drop service",
                    $traceId
                );

                return $result;
            }
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
                    'router_id' => $routerboard->id ?? null,
                    'filter_id' => $id ?? null
                ],
                500,
                "Failed to remove filter drop service",
                $traceId
            );
            throw $e;
        }
    }
}
