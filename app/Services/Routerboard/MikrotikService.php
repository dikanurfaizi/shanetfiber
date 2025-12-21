<?php

namespace App\Services\Routerboard;

use App\Helpers\ExecutionTimer;
use RouterOS\Client;
use RouterOS\Config;
use App\Helpers\LogHelper;

class MikrotikService
{
    public static function getMikrotik($mikrotik, $traceId)
    {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(null, [
            'host' => $mikrotik->host,
            'port' => $mikrotik->port,
            'username' => $mikrotik->username,
        ], 200, 'MikrotikService::getMikrotik - START', $traceId, $timer->lap());

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
            ], 200, 'MikrotikService::getMikrotik - SUCCESS', $traceId, $timer->lap());

            return $client;
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'trace_id' => $traceId,
                'error' => $e->getMessage(),
                'host' => $mikrotik->host,
                'exception' => get_class($e)
            ], 500, 'MikrotikService::getMikrotik - FAILED', $traceId, $timer->lap());
            throw $e;
        }
    }

    public static function testConnection($mikrotik, $traceId)
    {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(null, [
            'trace_id' => $traceId,
            'host' => $mikrotik['host'],
            'port' => $mikrotik['port'],
            'username' => $mikrotik['username']
        ], 200, 'MikrotikService::testConnection - START', $traceId, $timer->lap());

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
            ], 200, 'MikrotikService::testConnection - SUCCESS', $traceId, $timer->lap());

            return $client;
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'trace_id' => $traceId,
                'error' => $e->getMessage(),
                'host' => $mikrotik['host'],
                'exception' => get_class($e)
            ], 500, 'MikrotikService::testConnection - FAILED', $traceId, $timer->lap());
            throw $e;
        }
    }
}
