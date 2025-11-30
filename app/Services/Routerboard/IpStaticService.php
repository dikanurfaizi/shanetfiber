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
use App\Services\Routerboard\MikrotikService;

class IpStaticService
{
    public function addIpToAddressList($mikrotik, $ip_address, $address_list, $comment, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $query = new Query('/ip/firewall/address-list/add');
            $query->equal('list', $address_list);
            $query->equal('address', $ip_address);
            $query->equal('comment', $comment);
            $response = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'ip_address' => $ip_address,
                    'address_list' => $address_list,
                    'comment' => $comment,
                    'response' => $response
                ],
                200,
                "IP added to address list successfully",
                $traceId
            );

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'ip_address' => $ip_address,
                    'address_list' => $address_list,
                    'comment' => $comment,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to add IP to address list",
                $traceId
            );
            throw $e;
        }
    }

    public function deleteIpFromAddressList($mikrotik, $ip_address, $addresst_list_name, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $query = new Query('/ip/firewall/address-list/print');
            $query->where('address', $ip_address);
            $query->where('list', $addresst_list_name);
            $results = $client->query($query)->read();

            if (empty($results)) {
                LogHelper::logWarning(
                    null,
                    [
                        'ip_address' => $ip_address,
                        'address_list' => $addresst_list_name
                    ],
                    404,
                    "IP not found in address list",
                    $traceId
                );
                return null;
            }

            foreach ($results as $result) {
                $query = (new Query('/ip/firewall/address-list/remove'))
                    ->where('address', $ip_address)
                    ->equal('.id', $result['.id']);
                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'ip_address' => $ip_address,
                        'address_list' => $addresst_list_name,
                        'response' => $response
                    ],
                    200,
                    "IP removed from address list successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'ip_address' => $ip_address,
                    'address_list' => $addresst_list_name,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to delete IP from address list",
                $traceId
            );
            throw $e;
        }
    }

    public function deleteIpFromSimpleQueue($mikrotik, $simpleque_id, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $simplequeues = new Query('/queue/simple/print');
            $simplequeues->where('.id', $simpleque_id);
            $simplequeues = $client->query($simplequeues)->read();

            if (empty($simplequeues)) {
                LogHelper::logWarning(
                    null,
                    ['simpleque_id' => $simpleque_id],
                    404,
                    "Simple queue not found",
                    $traceId
                );
                return null;
            }

            foreach ($simplequeues as $simplequeue) {
                $query = (new Query('/queue/simple/remove'))
                    ->where('.id', $simpleque_id)
                    ->equal('.id', $simplequeue['.id']);

                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'simpleque_id' => $simpleque_id,
                        'response' => $response
                    ],
                    200,
                    "IP removed from simple queue successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'simpleque_id' => $simpleque_id,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to delete IP from simple queue",
                $traceId
            );
            throw $e;
        }
    }

    public function getAddressListByIpAddress($mikrotik, $ip_address, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $address_lists = (new Query('/ip/firewall/address-list/print'))
                ->where('address', $ip_address);
            $address_list = $client->query($address_lists)->read();

            if (empty($address_list)) {
                LogHelper::logWarning(
                    null,
                    ['ip_address' => $ip_address],
                    404,
                    "IP not found in any address list",
                    $traceId
                );
                return 'false';
            }

            return $address_list;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'ip_address' => $ip_address,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get address list by IP",
                $traceId
            );
            throw $e;
        }
    }

    public function getDataByIpAddressWithAddressListName($mikrotik, $ip_address, $address_list_name, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $address_lists = (new Query('/ip/firewall/address-list/print'))
                ->where('address', $ip_address)
                ->where('list', $address_list_name);
            $address_list = $client->query($address_lists)->read();

            if (empty($address_list)) {
                LogHelper::logWarning(
                    null,
                    [
                        'ip_address' => $ip_address,
                        'address_list_name' => $address_list_name
                    ],
                    404,
                    "IP not found in specified address list",
                    $traceId
                );
                return 'false';
            }

            return $address_list;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'ip_address' => $ip_address,
                    'address_list_name' => $address_list_name,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get data by IP and address list name",
                $traceId
            );
            throw $e;
        }
    }

    public function setCommentSimpleque($mikrotik, $simpleque_id, $comment, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $simpleques = (new Query('/queue/simple/print'))
                ->where('.id', $simpleque_id);
            $results = $client->query($simpleques)->read();

            if (empty($results)) {
                LogHelper::logWarning(
                    null,
                    ['simpleque_id' => $simpleque_id],
                    404,
                    "Simple queue not found",
                    $traceId
                );
                return null;
            }

            foreach ($results as $result) {
                $edit = (new Query('/queue/simple/set'))
                    ->where('.id', $simpleque_id)
                    ->equal('.id', $result['.id'])
                    ->equal('comment', $comment);
                $response = $client->query($edit)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'simpleque_id' => $simpleque_id,
                        'comment' => $comment,
                        'response' => $response
                    ],
                    200,
                    "Comment set on simple queue successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'simpleque_id' => $simpleque_id,
                    'comment' => $comment,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to set comment on simple queue",
                $traceId
            );
            throw $e;
        }
    }

    public function setIpSimpleque($mikrotik, $simpleque_id, $ip_address, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $simpleques = (new Query('/queue/simple/print'))
                ->where('.id', $simpleque_id);
            $results = $client->query($simpleques)->read();

            if (empty($results)) {
                LogHelper::logWarning(
                    null,
                    ['simpleque_id' => $simpleque_id],
                    404,
                    "Simple queue not found",
                    $traceId
                );
                return null;
            }

            foreach ($results as $result) {
                $edit = (new Query('/queue/simple/set'))
                    ->where('.id', $simpleque_id)
                    ->equal('.id', $result['.id'])
                    ->equal('target', $ip_address);
                $response = $client->query($edit)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'simpleque_id' => $simpleque_id,
                        'ip_address' => $ip_address,
                        'response' => $response
                    ],
                    200,
                    "IP set on simple queue successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'simpleque_id' => $simpleque_id,
                    'ip_address' => $ip_address,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to set IP on simple queue",
                $traceId
            );
            throw $e;
        }
    }

    public function setIpAddressAndMacAddress($mikrotik, $arp_id, $ip_address, $mac_address, $interface, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $query = new Query('/ip/arp/print');
            $query->where('.id', $arp_id);
            $results = $client->query($query)->read();

            if (empty($results)) {
                LogHelper::logWarning(
                    null,
                    ['arp_id' => $arp_id],
                    404,
                    "ARP entry not found",
                    $traceId
                );
                return null;
            }

            foreach ($results as $result) {
                $query = (new Query('/ip/arp/set'))
                    ->where('.id', $arp_id)
                    ->equal('.id', $result['.id'])
                    ->equal('address', $ip_address)
                    ->equal('mac-address', $mac_address)
                    ->equal('interface', $interface);
                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'arp_id' => $arp_id,
                        'ip_address' => $ip_address,
                        'mac_address' => $mac_address,
                        'interface' => $interface,
                        'response' => $response
                    ],
                    200,
                    "IP and MAC address set successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'arp_id' => $arp_id,
                    'ip_address' => $ip_address,
                    'mac_address' => $mac_address,
                    'interface' => $interface,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to set IP and MAC address",
                $traceId
            );
            throw $e;
        }
    }

    public function addIpToArp($mikrotik, $ip_address, $mac_address, $interface, $comment, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $query = new Query('/ip/arp/add');
            $query->equal('address', $ip_address);
            $query->equal('mac-address', $mac_address);
            $query->equal('interface', $interface);
            $query->equal('comment', $comment);
            $response = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'ip_address' => $ip_address,
                    'mac_address' => $mac_address,
                    'interface' => $interface,
                    'comment' => $comment,
                    'response' => $response
                ],
                200,
                "IP added to ARP successfully",
                $traceId
            );

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'ip_address' => $ip_address,
                    'mac_address' => $mac_address,
                    'interface' => $interface,
                    'comment' => $comment,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to add IP to ARP",
                $traceId
            );
            throw $e;
        }
    }

    public function deleteIpFromArp($mikrotik, $arp_id, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $query = new Query('/ip/arp/print');
            $query->where('.id', $arp_id);
            $results = $client->query($query)->read();

            if (empty($results)) {
                LogHelper::logWarning(
                    null,
                    ['arp_id' => $arp_id],
                    404,
                    "ARP entry not found",
                    $traceId
                );
                return null;
            }

            foreach ($results as $result) {
                $query = (new Query('/ip/arp/remove'))
                    ->where('.id', $arp_id)
                    ->equal('.id', $result['.id']);
                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'arp_id' => $arp_id,
                        'response' => $response
                    ],
                    200,
                    "IP removed from ARP successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'arp_id' => $arp_id,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to delete IP from ARP",
                $traceId
            );
            throw $e;
        }
    }

    public function getInterfaces($mikrotik, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $query = new Query('/interface/print');
            $query->where('type', 'ether');
            $query->where('type', 'vlan');
            $query->operations('|');
            $response = $client->query($query)->read();

            if (empty($response)) {
                LogHelper::logWarning(
                    null,
                    [],
                    404,
                    "No interfaces found",
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
                "Failed to get interfaces",
                $traceId
            );
            throw $e;
        }
    }

    public function getIpByArpId($mikrotik, $id, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $query = new Query('/ip/arp/print');
            $query->where('.id', $id);
            $getARP = $client->query($query)->read();

            if (empty($getARP)) {
                LogHelper::logWarning(
                    null,
                    ['arp_id' => $id],
                    404,
                    "ARP entry not found",
                    $traceId
                );
                return 'error';
            }

            return $getARP;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'arp_id' => $id,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get IP by ARP ID",
                $traceId
            );
            throw $e;
        }
    }

    public function getAddressList($mikrotik, string $traceId)
    {
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            $addresslist = new Query('/ip/firewall/address-list/print');
            $response = $client->query($addresslist)->read();

            if (empty($response)) {
                LogHelper::logWarning(
                    null,
                    [],
                    404,
                    "No address lists found",
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
                "Failed to get address lists",
                $traceId
            );
            throw $e;
        }
    }
}
