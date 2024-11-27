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

class IpStaticService
{
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws BadCredentialsException
     * @throws QueryException
     * @throws ConfigException
     * @throws Exception
     */
    public function getMikrotik($mikrotik): Client
    {
        if (!$mikrotik) {
            throw new Exception('Mikrotik not found.');
        }
        $config = (new Config())
            ->set('timeout', 2)
            ->set('host', $mikrotik->host)
            ->set('port', (int) $mikrotik->port)
            ->set('pass', $mikrotik->password)
            ->set('user', $mikrotik->username);
        return new Client($config);
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */


    //public function getPppSecrests($router)
    public function addIpToAddressList($mikrotik, $ip_address, $address_list, $comment)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/ip/firewall/address-list/add');
        $query->equal('list', $address_list);
        $query->equal('address', $ip_address);
        $query->equal('comment', $comment);
        return ($client->query($query)->read());
    }


    public function deleteIpFromAddressList($mikrotik, $ip_address, $addresst_list_name)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/ip/firewall/address-list/print');
        $query->where('address', $ip_address);
        $query->where('list', $addresst_list_name);
        $results = $client->query($query)->read();
        foreach ($results as $result) {
            $query = (new Query('/ip/firewall/address-list/remove'))
                ->where('address', $ip_address)
                ->equal('.id', $result['.id']);
            return $client->query($query)->read();
        }
    }
    /*  public function deleteIpFromSimpleQueue($mikrotik, $simpleque_id)
    {

        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/queue/simple/print');
        $query->where('.id', $simpleque_id);
        // $query->where('target', '192.168.100.250');
        $results = $client->query($query)->read();
        foreach ($results as $result) {
            $query = (new Query('/queue/simple/remove'))
                ->where('.id', $simpleque_id)
                ->equal('.id', $result['.id']);
            return $client->query($query)->read();
        }
    }
*/
    public function deleteIpFromSimpleQueue($mikrotik, $simpleque_id)
    {

        $client = $this->getMikrotik($mikrotik);
        $simplequeues = new Query('/queue/simple/print');
        $simplequeues->where('.id', $simpleque_id);
        $simplequeues = $client->query($simplequeues)->read();
        foreach ($simplequeues as $simplequeue) {
            $client = $this->getMikrotik($mikrotik);
            $query = (new Query('/queue/simple/remove'))
                ->where('.id', $simpleque_id)
                ->equal('.id', $simplequeue['.id']);

            return $client->query($query)->read();
        }
    }

    public function getAddressListByIpAddress($mikrotik, $ip_address)
    {
        $client = $this->getMikrotik($mikrotik);
        $address_lists = (new Query('/ip/firewall/address-list/print'))
            ->where('address', $ip_address);
        $address_list = $client->query($address_lists)->read();
        if ($address_list == []) {
            return ($response = 'false');
        } else {
            return $address_list;
        }
    }
    public function getDataByIpAddressWithAddressListName($mikrotik, $ip_address, $address_list_name)
    {
        $client = $this->getMikrotik($mikrotik);
        $address_lists = (new Query('/ip/firewall/address-list/print'))
            ->where('address', $ip_address)
            ->where('list', $address_list_name);
        $address_list = $client->query($address_lists)->read();
        if ($address_list == []) {
            return ($response = 'false');
        } else {
            return $address_list;
        }
    }

    public function setCommentSimpleque($mikrotik, $simpleque_id, $comment)
    {
        $client = $this->getMikrotik($mikrotik);
        $simpleques = (new Query('/queue/simple/print'))
            ->where('.id', $simpleque_id);
        $results = $client->query($simpleques)->read();
        foreach ($results as $result) {
            $edit = (new Query('/queue/simple/set'))
                ->where('.id', $simpleque_id)
                ->equal('.id', $result['.id'])
                ->equal('comment', $comment);
            return $client->query($edit)->read();
        }
    }

    public function setIpSimpleque($mikrotik, $simpleque_id, $ip_address)
    {
        $client = $this->getMikrotik($mikrotik);
        $simpleques = (new Query('/queue/simple/print'))
            ->where('.id', $simpleque_id);
        $results = $client->query($simpleques)->read();
        // return dd($results);
        foreach ($results as $result) {
            $edit = (new Query('/queue/simple/set'))
                ->where('.id', $simpleque_id)
                ->equal('.id', $result['.id'])
                ->equal('target', $ip_address);
            return $client->query($edit)->read();
        }
    }



    public function setIpAddressAndMacAddress($mikrotik, $arp_id, $ip_address, $mac_address, $interface)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/ip/arp/print');
        $query->where('.id', $arp_id);
        $results = $client->query($query)->read();
        foreach ($results as $result) {
            $query = (new Query('/ip/arp/set'))
                ->where('.id', $arp_id)
                ->equal('.id', $result['.id'])
                ->equal('address',  $ip_address)
                ->equal('mac-address', $mac_address)
                ->equal('interface', $interface);
            return $client->query($query)->read();
        }
    }
    public function addIpToArp($mikrotik, $ip_address, $mac_address, $interface, $comment)
    // public function addIpToArp($mikrotik, $ip_address, $interface, $comment)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/ip/arp/add');
        $query->equal('address', $ip_address);
        $query->equal('mac-address', $mac_address);
        $query->equal('interface', $interface);
        $query->equal('comment', $comment);
        return ($client->query($query)->read());
    }

    public function deleteIpFromArp($mikrotik, $arp_id)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/ip/arp/print');
        $query->where('.id', $arp_id);
        // $query->where('address', '192.168.100.250');
        $results = $client->query($query)->read();
        foreach ($results as $result) {
            $query = (new Query('/ip/arp/remove'))
                ->where('.id', $arp_id)
                ->equal('.id', $result['.id']);
            return $client->query($query)->read();
        }
    }

    public function getInterfaces($mikrotik)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/interface/print');
        $query->where('type', 'ether');
        $query->where('type', 'vlan');
        $query->operations('|');
        return $client->query($query)->read();
    }

    public function getIpByArpId($mikrotik, $id)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/ip/arp/print');

        $query->where('.id', $id);
        $getARP = $client->query($query)->read();
        if ($getARP == []) {
            return ($response = 'error');
        } else {
            return $getARP;
        }
    }


    //Firewall->Address List
    public function getAddressList($mikrotik)
    {
        $client = $this->getMikrotik($mikrotik);
        $addresslist = new Query('/ip/firewall/address-list/print');
        return $client->query($addresslist)->read();
    }
}
