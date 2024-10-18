<?php

namespace App\Services\Routerboard;

use App\Http\Resources\TrafficInterfaceResource;
use App\Models\Mikrotik;
use Exception;
use Illuminate\Http\JsonResponse;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;

class ScheduleService
{
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     * @throws Exception
     */
    public function getMikrotik($routerboard): Client
    {
        $mikrotik = $routerboard;
        if (!$mikrotik) {
            throw new Exception('Mikrotik not found.');
        }

        $config = (new Config())
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
    public function getAllSchedule($routerboard)
    {
        $client = $this->getMikrotik($routerboard);
        $query = new Query('/system/schedule/print');

        return $client->query($query)->read();

        //return value
        /*
 0 => array:10 [▼
    ".id" => "*0"
    "name" => "schedule1"
    "start-date" => "2023-09-16"
    "start-time" => "11:06:58"
    "interval" => "0s"
    "on-event" => ""
    "owner" => "admin"
    "policy" => "ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon"
    "run-count" => "0"
    "disabled" => "false"
  ]

        */
    }

    public function getScheduleByName($routerboard, $scheduleName)
    {

        $client = $this->getMikrotik($routerboard);
        $query = (new Query('/system/schedule/print'))
            ->where('name', $scheduleName);

        return $client->query($query)->read();
    }

    public function getScheduleById($routerboard, $scheduleId)
    {

        $client = $this->getMikrotik($routerboard);
        $query = (new Query('/system/schedule/print'))
            ->where('.id', $scheduleId);

        return $client->query($query)->read();
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function addScheduleEveryDay($routerboard, $nameSchedule, $nameScript)
    {
        $onEventSchedule = '/system script run ' . $nameScript . '';
        $client = $this->getMikrotik($routerboard);

        $query =
            (new Query('/system/schedule/add'))
            ->equal('name', $nameSchedule)
            ->equal('start-time', '01:00:00')
            ->equal('interval', '1d')
            ->equal('on-event', $onEventSchedule)
            ->equal('comment', 'create_by_customer_management');

        return $client->query($query)->read();
    }

    public function disabledSchedule($routerboard, $scheduleName, $disabledStatus)
    {
        $client = $this->getMikrotik($routerboard);
        $schedules = new Query('/system/schedule/print');
        $schedules->where('name', $scheduleName);
        $resultSchedules = $client->query($schedules)->read();
        foreach ($resultSchedules as $resultSchedule) {
            $client = $this->getMikrotik($routerboard);
            $query = (new Query('/system/schedule/set'))
                ->where('name', $scheduleName)
                ->equal('.id', $resultSchedule['.id'])
                ->equal('disabled', $disabledStatus);

            return $client->query($query)->read();
        }
    }

    public function disabledScheduleById($routerboard, $scheduleId, $disabledStatus)
    {
        $client = $this->getMikrotik($routerboard);
        $schedules = new Query('/system/schedule/print');
        $schedules->where('.id', $scheduleId);
        $resultSchedules = $client->query($schedules)->read();
        foreach ($resultSchedules as $schedule) {
            $client = $this->getMikrotik($routerboard);
            $query = (new Query('/system/schedule/set'))
                ->where('.id', $scheduleId)
                ->equal('.id', $schedule['.id'])
                ->equal('disabled', $disabledStatus);

            return $client->query($query)->read();
        }
    }

    public function removeSchedule($routerboard, $scheduleId)
    {

        $client = $this->getMikrotik($routerboard);
        $schedules = new Query('/system/schedule/print');
        $schedules->where('.id', $scheduleId);
        $resultSchedules = $client->query($schedules)->read();

        foreach ($resultSchedules as $schedule) {
            $client = $this->getMikrotik($routerboard);
            $query = (new Query('/system/schedule/remove'))
                ->where('.id', $scheduleId)
                ->equal('.id', $schedule['.id']);

            return $client->query($query)->read();
        }
    }
}
