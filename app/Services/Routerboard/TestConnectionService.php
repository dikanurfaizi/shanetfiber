<?php

namespace App\Services\Routerboard;

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;
use App\Helpers\LogHelper;

class TestConnectionService
{
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     * @throws \Exception
     */
    public function getMikrotik($router): ?Client
    {
        try {
            if (!$router) {
                throw new \Exception('Mikrotik not found.');
            }

            $config = (new Config())
                ->set('timeout', 2)
                ->set('host', $router->host)
                ->set('port', (int) $router->port)
                ->set('pass', $router->password)
                ->set('user', $router->username);

            $client = new Client($config);
            // LogHelper::logInfo(null, ['host' => $router->host], 200, "Successfully connected to Mikrotik");
            return $client;
        } catch (\Exception $e) {
            LogHelper::logError(null, ['host' => $router->host ?? 'N/A', 'error' => $e->getMessage()], 500, "Failed to connect to Mikrotik");
            return null;
        }
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function testConnection($router)
    {
        try {
            $config = (new Config())
                ->set('host', $router['host'])
                ->set('port', (int) $router['port'])
                ->set('pass', $router['password'])
                ->set('user', $router['username']);

            $client = new Client($config);
            $query = (new Query('/system/resource/print'));
            $result = $client->query($query)->read();

            // LogHelper::logInfo(null, ['host' => $router['host']], 200, "Successfully fetched system resource from Mikrotik");
            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(null, ['host' => $router['host'] ?? 'N/A', 'error' => $e->getMessage()], 500, "Failed to fetch system resource from Mikrotik");
            return [];
        }
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function checkOnline($router)
    {
        try {
            $client = $this->getMikrotik($router);
            if (!$client) {
                throw new \Exception('Mikrotik client is null. Connection failed.');
            }

            $query = (new Query('/system/resource/print'));
            $result = $client->query($query)->read();

            // LogHelper::logInfo(null, ['host' => $router->host], 200, "Mikrotik is online");
            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(null, ['host' => $router->host ?? 'N/A', 'error' => $e->getMessage()], 500, "Mikrotik is offline or unreachable");
            return [];
        }
    }
}

// class TestConnectionService
// {
//     /**
//      * @throws ClientException
//      * @throws ConnectException
//      * @throws QueryException
//      * @throws BadCredentialsException
//      * @throws ConfigException
//      * @throws \Exception
//      */
//     public function getMikrotik($router): Client
//     {
//         $mikrotik = $router;
//         if (!$mikrotik) {
//             throw new \Exception('Mikrotik not found.');
//         }

//         $config = (new Config())
//             ->set('timeout', 2)
//             ->set('host', $mikrotik->host)
//             ->set('port', (int) $mikrotik->port)
//             ->set('pass', $mikrotik->password)
//             ->set('user', $mikrotik->username);

//         return new Client($config);
//     }

//     /**
//      * @throws ClientException
//      * @throws ConnectException
//      * @throws QueryException
//      * @throws BadCredentialsException
//      * @throws ConfigException
//      */
//     public function testConnection($router)
//     {
//         $config = (new Config())
//             ->set('host', $router['host'])
//             ->set('port', (int) $router['port'])
//             ->set('pass', $router['password'])
//             ->set('user', $router['username']);
//         $client = new Client($config);

//         $query = (new Query('/system/resource/print'));

//         return $client->query($query)->read();
//     }

//     /**
//      * @throws ClientException
//      * @throws ConnectException
//      * @throws QueryException
//      * @throws BadCredentialsException
//      * @throws ConfigException
//      */
//     public function checkOnline($router)
//     {
//         $client = $this->getMikrotik($router);
//         $query = (new Query('/system/resource/print'));

//         return $client->query($query)->read();
//     }
// }
