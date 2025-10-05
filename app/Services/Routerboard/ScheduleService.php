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

class ScheduleService
{
    public function getAllSchedule($routerboard, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = new Query('/system/schedule/print');
            $schedules = $client->query($query)->read();

            if (empty($schedules)) {
                LogHelper::logWarning(
                    null,
                    [],
                    404,
                    "No schedules found",
                    $traceId
                );
                return [];
            }

            return $schedules;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get all schedules",
                $traceId
            );
            throw $e;
        }
    }

    public function getScheduleByName($routerboard, $scheduleName, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = (new Query('/system/schedule/print'))
                ->where('name', $scheduleName);
            $schedule = $client->query($query)->read();

            if (empty($schedule)) {
                LogHelper::logWarning(
                    null,
                    ['schedule_name' => $scheduleName],
                    404,
                    "Schedule not found by name",
                    $traceId
                );
                return [];
            }

            return $schedule;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'schedule_name' => $scheduleName,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get schedule by name",
                $traceId
            );
            throw $e;
        }
    }

    public function getScheduleById($routerboard, $scheduleId, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = (new Query('/system/schedule/print'))
                ->where('.id', $scheduleId);
            $schedule = $client->query($query)->read();

            if (empty($schedule)) {
                LogHelper::logWarning(
                    null,
                    ['schedule_id' => $scheduleId],
                    404,
                    "Schedule not found by ID",
                    $traceId
                );
                return [];
            }

            return $schedule;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'schedule_id' => $scheduleId,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get schedule by ID",
                $traceId
            );
            throw $e;
        }
    }

    public function addScheduleEveryDay($routerboard, $nameSchedule, $nameScript, string $traceId)
    {
        try {
            $onEventSchedule = '/system script run ' . $nameScript . '';
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);

            $query = (new Query('/system/schedule/add'))
                ->equal('name', $nameSchedule)
                ->equal('start-time', '01:00:00')
                ->equal('interval', '1d')
                ->equal('on-event', $onEventSchedule)
                ->equal('comment', 'create_by_customer_management');

            $response = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'name_schedule' => $nameSchedule,
                    'name_script' => $nameScript,
                    'response' => $response
                ],
                200,
                "Daily schedule added successfully",
                $traceId
            );

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'name_schedule' => $nameSchedule,
                    'name_script' => $nameScript,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to add daily schedule",
                $traceId
            );
            throw $e;
        }
    }

    public function disabledSchedule($routerboard, $scheduleName, $disabledStatus, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $schedules = new Query('/system/schedule/print');
            $schedules->where('name', $scheduleName);
            $resultSchedules = $client->query($schedules)->read();

            if (empty($resultSchedules)) {
                LogHelper::logWarning(
                    null,
                    ['schedule_name' => $scheduleName],
                    404,
                    "Schedule not found for disable/enable",
                    $traceId
                );
                return null;
            }

            foreach ($resultSchedules as $resultSchedule) {
                $query = (new Query('/system/schedule/set'))
                    ->where('name', $scheduleName)
                    ->equal('.id', $resultSchedule['.id'])
                    ->equal('disabled', $disabledStatus);

                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'schedule_name' => $scheduleName,
                        'disabled_status' => $disabledStatus,
                        'response' => $response
                    ],
                    200,
                    "Schedule status updated successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'schedule_name' => $scheduleName,
                    'disabled_status' => $disabledStatus,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to update schedule status",
                $traceId
            );
            throw $e;
        }
    }

    public function disabledScheduleById($routerboard, $scheduleId, $disabledStatus, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $schedules = new Query('/system/schedule/print');
            $schedules->where('.id', $scheduleId);
            $resultSchedules = $client->query($schedules)->read();

            if (empty($resultSchedules)) {
                LogHelper::logWarning(
                    null,
                    ['schedule_id' => $scheduleId],
                    404,
                    "Schedule not found for disable/enable by ID",
                    $traceId
                );
                return null;
            }

            foreach ($resultSchedules as $schedule) {
                $query = (new Query('/system/schedule/set'))
                    ->where('.id', $scheduleId)
                    ->equal('.id', $schedule['.id'])
                    ->equal('disabled', $disabledStatus);

                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'schedule_id' => $scheduleId,
                        'disabled_status' => $disabledStatus,
                        'response' => $response
                    ],
                    200,
                    "Schedule status updated successfully by ID",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'schedule_id' => $scheduleId,
                    'disabled_status' => $disabledStatus,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to update schedule status by ID",
                $traceId
            );
            throw $e;
        }
    }

    public function removeSchedule($routerboard, $scheduleId, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $schedules = new Query('/system/schedule/print');
            $schedules->where('.id', $scheduleId);
            $resultSchedules = $client->query($schedules)->read();

            if (empty($resultSchedules)) {
                LogHelper::logWarning(
                    null,
                    ['schedule_id' => $scheduleId],
                    404,
                    "Schedule not found for removal",
                    $traceId
                );
                return null;
            }

            foreach ($resultSchedules as $schedule) {
                $query = (new Query('/system/schedule/remove'))
                    ->where('.id', $scheduleId)
                    ->equal('.id', $schedule['.id']);

                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'schedule_id' => $scheduleId,
                        'response' => $response
                    ],
                    200,
                    "Schedule removed successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'schedule_id' => $scheduleId,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to remove schedule",
                $traceId
            );
            throw $e;
        }
    }
}
