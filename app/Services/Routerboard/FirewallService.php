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

class FirewallService
{
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     * @throws Exception
     */
    public function getMikrotik($routerboard): Client
    {
        $mikrotik = $routerboard;
        if (!$mikrotik) {
            throw new Exception('Mikrotik not found.');
        }

        $config = (new Config())
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
    public function getIpAddressList($routerboard)
    {
        $client = $this->getMikrotik($routerboard);
        $query = (new query('/ip/firewall/address-list/print'))
            ->where('disabled', 'false');
        return $client->query($query)->read();
    }

    public function createNatService($routerboard, $dstAddress, $srcAddressList, $dstAddressList)
    {
        $client = $this->getMikrotik($routerboard);
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
        return $client->query($query)->read();
    }

    public function updateNatService($routerboard, $dstAddress, $srcAddressList, $dstAddressList, $id)
    {
        $client = $this->getMikrotik($routerboard);
        $natService = new Query('/ip/firewall/nat/print');
        $natService->where('.id', $id);
        $results = $client->query($natService)->read();

        foreach ($results as $result) {
            $client = $this->getMikrotik($routerboard);
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


            return $client->query($query)->read();
        }
    }

    public function createFilterDropService($routerboard, $srcAddressList, $dstAddress)
    {
        $client = $this->getMikrotik($routerboard);
        $query = new Query('/ip/firewall/filter/add');
        $query->equal('protocol', 'tcp');
        $query->equal('chain', 'forward');
        // $query->equal('dst-port', '443');
        $query->equal('dst-address', '!' . $dstAddress);
        $query->equal('src-address-list', $srcAddressList);
        $query->equal('action', 'drop');

        $query->equal('comment', 'Create from customer management to drop internet');
        return $client->query($query)->read();
    }

    public function updateFilterDropService($routerboard, $srcAddressList, $dstAddress, $id)
    {
        $client = $this->getMikrotik($routerboard);
        $filterService = new Query('/ip/firewall/filter/print');
        $filterService->where('.id', $id);
        $results = $client->query($filterService)->read();

        foreach ($results as $result) {
            $client = $this->getMikrotik($routerboard);
            $query = new Query('/ip/firewall/filter/set');
            $query->where('.id', $id);
            $query->equal('.id', $result['.id']);
            $query->equal('dst-address', '!' . $dstAddress);
            $query->equal('src-address-list', $srcAddressList);
            return $client->query($query)->read();
        }
    }

    public function getNatService($routerboard)
    {
        $client = $this->getMikrotik($routerboard);
        $query = (new Query('/ip/firewall/nat/print'));
        return $client->query($query)->read();
    }

    public function removeNatSevice($routerboard, $id)
    {
        $client = $this->getMikrotik($routerboard);
        $natService = new Query('/ip/firewall/nat/print');
        $natService->where('.id', $id);
        $results = $client->query($natService)->read();

        foreach ($results as $result) {
            $client = $this->getMikrotik($routerboard);
            $query = (new Query('/ip/firewall/nat/remove'))
                ->where('.id', $id)
                ->equal('.id', $result['.id']);

            return $client->query($query)->read();
        }
    }

    public function removeFilterDropSevice($routerboard, $id)
    {
        $client = $this->getMikrotik($routerboard);
        $filterService = new Query('/ip/firewall/filter/print');
        $filterService->where('.id', $id);
        $results = $client->query($filterService)->read();

        foreach ($results as $result) {
            $client = $this->getMikrotik($routerboard);
            $query = (new Query('/ip/firewall/filter/remove'))
                ->where('.id', $id)
                ->equal('.id', $result['.id']);

            return $client->query($query)->read();
        }
    }
}
