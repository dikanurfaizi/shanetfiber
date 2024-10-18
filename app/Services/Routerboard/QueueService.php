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

class QueueService
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
        // $mikrotik = $router;
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
    public function getParentSimpleQueues($mikrotik)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/queue/simple/print');
        $query->where('parent', 'none')->where('disabled', 'false');
        $parentSimpleQueue = $client->query($query)->read();
        if ($parentSimpleQueue == []) {
            return ($response = 'error');
        } else {
            return $parentSimpleQueue;
        }
    }

    public function getQueueTypes($mikrotik)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/queue/type/print');
        $parentSimpleQueue = $client->query($query)->read();
        if ($parentSimpleQueue == []) {
            return ($response = 'error');
        } else {
            return $parentSimpleQueue;
        }
    }
}
