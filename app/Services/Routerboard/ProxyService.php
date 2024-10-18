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

class ProxyService
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


    public function createAccessServiceV6_49($routerboard, $srcAddress, $natDstAddress, $action)
    {
        $client = $this->getMikrotik($routerboard);

        $query = new Query('/ip/proxy/access/add');
        $query->equal('src-address', $srcAddress);
        $query->equal('action', $action);
        if ($action == 'deny') {
            $query->equal('redirect-to', $natDstAddress . '/isolir');
        } else if ($action == 'allow') {
            $query->equal('!redirect-to');
        }
        return $client->query($query)->read();
    }

    public function createAccessServiceV7_10($routerboard, $srcAddress, $natDstAddress, $action)
    {
        $client = $this->getMikrotik($routerboard);

        $query = new Query('/ip/proxy/access/add');
        $query->equal('src-address', $srcAddress);
        $query->equal('action', $action);
        if ($action == 'redirect') {
            $query->equal('action-data', $natDstAddress . '/isolir');
        }
        return $client->query($query)->read();
    }
    public function updateAccessServiceV7_10($routerboard, $srcAddress, $natDstAddress, $id, $action)
    {

        $client = $this->getMikrotik($routerboard);
        $accessService = new Query('/ip/proxy/access/print');
        $accessService->where('.id', $id);
        $results = $client->query($accessService)->read();

        foreach ($results as $result) {
            $client = $this->getMikrotik($routerboard);
            $query = new Query('/ip/proxy/access/set');
            $query->where('.id', $id);
            $query->equal('.id', $result['.id']);
            $query->equal('src-address', $srcAddress);
            $query->equal('action', $action);
            if ($action == 'redirect') {
                $query->equal('action-data', $natDstAddress . '/isolir');
            }
            return $client->query($query)->read();
        }
    }

    public function updateAccessServiceV6_49($routerboard, $srcAddress, $natDstAddress, $id, $action)
    {

        $client = $this->getMikrotik($routerboard);
        $accessService = new Query('/ip/proxy/access/print');
        $accessService->where('.id', $id);
        $results = $client->query($accessService)->read();


        foreach ($results as $result) {
            $client = $this->getMikrotik($routerboard);
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
            return $client->query($query)->read();
        }
    }

    public function removeAccessService($routerboard, $id)
    {
        $client = $this->getMikrotik($routerboard);
        $accessService = new Query('/ip/proxy/access/print');
        $accessService->where('.id', $id);
        $results = $client->query($accessService)->read();

        foreach ($results as $result) {
            $client = $this->getMikrotik($routerboard);
            $query = (new Query('/ip/proxy/access/remove'))
                ->where('.id', $id)
                ->equal('.id', $result['.id']);

            return $client->query($query)->read();
        }
    }

    public function getAccessService($routerboard)
    {
        $client = $this->getMikrotik($routerboard);
        $accessService = new Query('/ip/proxy/access/print');
        return $client->query($accessService)->read();
    }

    public function enableProxyService($routerboard)
    {
        $client = $this->getMikrotik($routerboard);
        $query = (new query('/ip/proxy/set'))
            ->equal('enabled', 'true')
            ->equal('port', '8080');
        $client->query($query)->read();
    }

    public function disableProxyService($routerboard)
    {
        $client = $this->getMikrotik($routerboard);
        $query = (new query('/ip/proxy/set'))
            ->equal('enabled', 'false');
        $client->query($query)->read();
    }
}
