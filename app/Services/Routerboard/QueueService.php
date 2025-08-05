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
use App\Helpers\LogHelper;

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
    public function getMikrotik($mikrotik, string $traceId): Client
    {
        try {
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
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'port' => $mikrotik->port ?? 'N/A',
                    'error' => $e->getMessage()
                ],
                503,
                "Failed to connect to Mikrotik",
                $traceId
            );
            throw $e;
        }
    }

    public function getParentSimpleQueues($mikrotik, string $traceId)
    {
        try {
            $client = $this->getMikrotik($mikrotik, $traceId);
            $query = new Query('/queue/simple/print');
            $query->where('parent', 'none')->where('disabled', 'false');
            $parentSimpleQueue = $client->query($query)->read();

            if (empty($parentSimpleQueue)) {
                LogHelper::logWarning(
                    null,
                    [],
                    404,
                    "No parent simple queues found",
                    $traceId
                );
                return 'error';
            }

            return $parentSimpleQueue;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get parent simple queues",
                $traceId
            );
            throw $e;
        }
    }

    public function getQueueTypes($mikrotik, string $traceId)
    {
        try {
            $client = $this->getMikrotik($mikrotik, $traceId);
            $query = new Query('/queue/type/print');
            $queueTypes = $client->query($query)->read();

            if (empty($queueTypes)) {
                LogHelper::logWarning(
                    null,
                    [],
                    404,
                    "No queue types found",
                    $traceId
                );
                return 'error';
            }

            return $queueTypes;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get queue types",
                $traceId
            );
            throw $e;
        }
    }

    public function createSimpleQueue($mikrotik, $name, $target, $parent, $priority, $queuetype, $limitat, $maxlimit, $comment, string $traceId)
    {
        try {
            $client = $this->getMikrotik($mikrotik, $traceId);
            $query = (new Query('/queue/simple/add'))
                ->equal('name', $name)
                ->equal('target', $target)
                ->equal('parent', $parent)
                ->equal('queue', $queuetype)
                ->equal('priority', $priority)
                ->equal('limit-at', $limitat)
                ->equal('max-limit', $maxlimit)
                ->equal('comment', $comment);

            $response = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'name' => $name,
                    'target' => $target,
                    'parent' => $parent,
                    'queue_type' => $queuetype,
                    'priority' => $priority,
                    'limit_at' => $limitat,
                    'max_limit' => $maxlimit,
                    'comment' => $comment,
                    'response' => $response
                ],
                200,
                "Simple queue created successfully",
                $traceId
            );

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'name' => $name,
                    'target' => $target,
                    'parent' => $parent,
                    'queue_type' => $queuetype,
                    'priority' => $priority,
                    'limit_at' => $limitat,
                    'max_limit' => $maxlimit,
                    'comment' => $comment,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to create simple queue",
                $traceId
            );
            throw $e;
        }
    }

    public function setLimitSimplequeById($mikrotik, $simpleque_id, $parent, $priority, $queuetype, $limitat, $maxlimit, string $traceId)
    {
        try {
            $client = $this->getMikrotik($mikrotik, $traceId);
            $simpleques = (new Query('/queue/simple/print'))
                ->where('.id', $simpleque_id);
            $results = $client->query($simpleques)->read();

            if (empty($results)) {
                LogHelper::logWarning(
                    null,
                    ['simpleque_id' => $simpleque_id],
                    404,
                    "Simple queue not found for update",
                    $traceId
                );
                return null;
            }

            foreach ($results as $result) {
                $edit = (new Query('/queue/simple/set'))
                    ->where('.id', $simpleque_id)
                    ->equal('.id', $result['.id'])
                    ->equal('parent', $parent)
                    ->equal('queue', $queuetype)
                    ->equal('priority', $priority)
                    ->equal('limit-at', $limitat)
                    ->equal('max-limit', $maxlimit);

                $response = $client->query($edit)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'simpleque_id' => $simpleque_id,
                        'parent' => $parent,
                        'queue_type' => $queuetype,
                        'priority' => $priority,
                        'limit_at' => $limitat,
                        'max_limit' => $maxlimit,
                        'response' => $response
                    ],
                    200,
                    "Simple queue limits updated successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'simpleque_id' => $simpleque_id,
                    'parent' => $parent,
                    'queue_type' => $queuetype,
                    'priority' => $priority,
                    'limit_at' => $limitat,
                    'max_limit' => $maxlimit,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to update simple queue limits",
                $traceId
            );
            throw $e;
        }
    }
}
