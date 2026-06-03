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
     */
    public function testConnection($mikrotik, string $traceId)
    {
        try {
            $client = MikrotikService::testConnection($mikrotik, $traceId);

            $query = (new Query('/system/resource/print'));
            $result = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'host' => $mikrotik['host'],
                    'resource_data' => array_keys($result[0] ?? [])
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
                        // PERBAIKAN: Menggunakan array key yang benar sesuai parameter $mikrotik
                        'host' => $mikrotik['host'] ?? 'N/A',
                        'port' => $mikrotik['port'] ?? 'N/A'
                    ]
                ],
                500,
                "Failed to fetch system resource from Mikrotik",
                $traceId
            );
            return []; // Pastikan return array kosong jika gagal
        }
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function checkOnline($router, string $traceId): array
    {
        try {
            $client = MikrotikService::getMikrotik($router, $traceId);

            if (!$client) {
                LogHelper::LogError(
                    null,
                    [
                        'status' => false,
                        'message' => "Failed to connect to Mikrotik: {$router->name}",
                        'data' => null,
                    ],
                    503,
                    "Mikrotik is offline",
                    $traceId
                );
                return [
                    'status' => false,
                    'message' => "Failed to connect to Mikrotik: {$router->name}",
                    'data' => null,
                ];
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

            return [
                'status' => true,
                'message' => 'Mikrotik is online',
                'data' => $result[0] ?? [],
            ];
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

            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }
}
