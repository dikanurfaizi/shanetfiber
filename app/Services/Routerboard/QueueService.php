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

    public function createSimpleQueue($mikrotik, $name, $target, $parent, $priority, $queuetype, $limitat, $maxlimit, $comment)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = (new Query('/queue/simple/add'))
            ->equal('name', $name)
            ->equal('target', $target)
            ->equal('parent', $parent)
            ->equal('queue', $queuetype)
            ->equal('priority', $priority)
            ->equal('limit-at', $limitat)
            ->equal('max-limit', $maxlimit)
            ->equal('comment', $comment);

        return $client->query($query)->read();
    }



    public function setLimitSimplequeById($mikrotik, $simpleque_id, $parent, $priority, $queuetype, $limitat, $maxlimit)
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
                ->equal('parent', $parent)
                ->equal('queue', $queuetype)
                ->equal('priority', $priority)
                ->equal('limit-at', $limitat)
                ->equal('max-limit', $maxlimit);
            return $client->query($edit)->read();
        }
    }
}
