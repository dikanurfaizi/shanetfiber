<?php

namespace App\Services\Routerboard;

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\StreamException;
use App\Helpers\LogHelper;
use App\Helpers\SnmpHelper;

class MikrotikConnectionService
{
    /**
     * Ambil instance Client dari RouterBoard
     */
    public static function getClient($routerboard, string $traceId): ?Client
    {
        $mikrotik = $routerboard;
        $ip = self::resolveIp($mikrotik->host, $traceId);

        if (!$ip) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A'],
                422,
                'IP not resolved',
                $traceId
            );
            return null;
        }

        try {
            if (!$mikrotik) {
                throw new \Exception('Mikrotik not found.');
            }

            $config = (new Config())
                ->set('host', $ip)
                ->set('port', (int) $mikrotik->port)
                ->set('pass', $mikrotik->password)
                ->set('user', $mikrotik->username);

            return new Client($config);
        } catch (StreamException $e) {
            LogHelper::logError(
                null,
                [
                    'ip' => $ip ?? 'N/A',
                    'host' => $mikrotik->host ?? 'N/A',
                    'port' => $mikrotik->port ?? 'N/A',
                    'error' => 'Stream Timeout: ' . $e->getMessage()
                ],
                504,
                "Failed to connect to Mikrotik due to stream timeout",
                $traceId
            );
            return null;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'ip' => $ip,
                    'host' => $mikrotik->host ?? 'N/A',
                    'port' => $mikrotik->port ?? 'N/A',
                    'error' => $e->getMessage()
                ],
                503,
                "Failed to connect to Mikrotik",
                $traceId
            );
            return null;
        }
    }

    /**
     * Resolve IP dari hostname (bisa pakai cache atau static array)
     */
    private static function resolveIp(string $host, string $traceId): ?string
    {
        $resolvedIp = SnmpHelper::resolveIp($host);

        if (!$resolvedIp) {
            LogHelper::logError(null, ['host' => $host], 404, 'IP resolution failed', $traceId);
            return null;
        }

        return $resolvedIp;
    }
}
