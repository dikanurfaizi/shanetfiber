<?php

namespace App\Services\Routerboard;

use RouterOS\Client;
use RouterOS\Config;
use App\Helpers\LogHelper;

class MikrotikService
{
    public static function getMikrotik($mikrotik, $traceId)
    {
        LogHelper::logInfo(null, [
            'host' => $mikrotik->host,
            'port' => $mikrotik->port,
            'username' => $mikrotik->username,
        ], 200, 'MikrotikService::getMikrotik - START', $traceId);

        try {
            $config = (new Config())
                ->set('timeout', 2)
                ->set('host', $mikrotik->host)
                ->set('port', (int) $mikrotik->port)
                ->set('pass', $mikrotik->password)
                ->set('user', $mikrotik->username);

            $client = new Client($config);

            LogHelper::logInfo(null, [
                'host' => $mikrotik->host,
                'status' => 'connected'
            ], 200, 'MikrotikService::getMikrotik - SUCCESS', $traceId);

            return $client;
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'trace_id' => $traceId,
                'error' => $e->getMessage(),
                'host' => $mikrotik->host,
                'exception' => get_class($e)
            ], 500, 'MikrotikService::getMikrotik - FAILED', $traceId);
            throw $e;
        }
    }

    public static function testConnection($mikrotik, $traceId)
    {
        LogHelper::logInfo(null, [
            'trace_id' => $traceId,
            'host' => $mikrotik['host'],
            'port' => $mikrotik['port'],
            'username' => $mikrotik['username']
        ], 200, 'MikrotikService::testConnection - START', $traceId);

        try {
            $config = (new Config())
                ->set('host', $mikrotik['host'])
                ->set('port', (int) $mikrotik['port'])
                ->set('pass', $mikrotik['password'])
                ->set('user', $mikrotik['username']);

            $client = new Client($config);

            LogHelper::logInfo(null, [
                'trace_id' => $traceId,
                'host' => $mikrotik['host'],
                'status' => 'connected'
            ], 200, 'MikrotikService::testConnection - SUCCESS', $traceId);

            return $client;
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'trace_id' => $traceId,
                'error' => $e->getMessage(),
                'host' => $mikrotik['host'],
                'exception' => get_class($e)
            ], 500, 'MikrotikService::testConnection - FAILED', $traceId);
            throw $e;
        }
    }
}
