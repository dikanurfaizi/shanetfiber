<?php

namespace App\Console\Commands;

use App\Helpers\ExecutionTimer;
use Illuminate\Console\Command;
use App\Helpers\LogHelper;
use Illuminate\Support\Str;

class SchedulerHeartbeat extends Command
{
    protected $signature = 'system:scheduler-heartbeat';
    protected $description = 'Record a heartbeat log for Laravel scheduler';

    public function handle(): void
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();
        LogHelper::logInfo(null, [
            'time' => now(),
            'hostname' => gethostname(),
            'sapi' => php_sapi_name(),
        ], 200, 'Scheduler heartbeat log', $traceId, $timer->lap());
    }
}
