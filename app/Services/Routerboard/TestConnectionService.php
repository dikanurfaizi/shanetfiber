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
    public function getMikrotik($router, string $traceId): ?Client
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

            LogHelper::logInfo(
                null,
                [
                    'host' => $router->host,
                    'port' => $router->port,
                    'connection_timeout' => '2s'
                ],
                200,
                "Successfully connected to Mikrotik",
                $traceId
            );

            return $client;
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
                    'connection_details' => [
                        'host' => $router->host ?? 'N/A',
                        'port' => $router->port ?? 'N/A'
                    ]
                ],
                500,
                "Failed to connect to Mikrotik",
                $traceId
            );
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
    public function testConnection($router, string $traceId)
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

            LogHelper::logInfo(
                null,
                [
                    'host' => $router['host'],
                    'resource_data' => array_keys($result[0] ?? []) // Log only keys for brevity
                ],
                200,
                "Successfully fetched system resource from Mikrotik",
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
                    'connection_details' => [
                        'host' => $router['host'] ?? 'N/A',
                        'port' => $router['port'] ?? 'N/A'
                    ]
                ],
                500,
                "Failed to fetch system resource from Mikrotik",
                $traceId
            );
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
    public function checkOnline($router, string $traceId)
    {
        try {
            $client = $this->getMikrotik($router, $traceId);
            if (!$client) {
                throw new \Exception('Mikrotik client is null. Connection failed.');
            }

            $query = (new Query('/system/resource/print'));
            $result = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'host' => $router->host,
                    'uptime' => $result[0]['uptime'] ?? 'N/A',
                    'cpu_load' => $result[0]['cpu-load'] ?? 'N/A'
                ],
                200,
                "Mikrotik is online and responsive",
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
                    'connection_details' => [
                        'host' => $router->host ?? 'N/A',
                        'port' => $router->port ?? 'N/A'
                    ]
                ],
                503,
                "Mikrotik is offline or unreachable",
                $traceId
            );
            return [];
        }
    }
}
