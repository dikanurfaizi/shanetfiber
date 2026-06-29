<?php

namespace App\Listeners;

use App\Events\MonthlyKickoffBroadcastTriggered;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Jobs\Notification\SendMonthlyKickOffBroadcastNotificationJob;

class QueueMonthlyKickoffBroadcast
{
    public function handle(MonthlyKickoffBroadcastTriggered $event): void
    {
        $timer = new ExecutionTimer();
        $action  = $event->action;
        $gatewayType = $event->gatewayType;
        $traceId = $event->traceId;

        LogHelper::logInfo(
            null,
            [
                'action'     => $action,
            ],
            200,
            'QueueMonthlyKickoffBroadcast listener triggered',
            $traceId,
            $timer->lap()
        );

        SendMonthlyKickOffBroadcastNotificationJob::dispatch(
            $action,
            $gatewayType,
            $traceId
        )->onQueue('notification');
    }
}
