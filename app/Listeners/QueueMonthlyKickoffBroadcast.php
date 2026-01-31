<?php

namespace App\Listeners;

use App\Events\MonthlyKickoffBroadcastTriggered;
use App\Helpers\LogHelper;
use App\Jobs\Notification\SendMonthlyKickOffBroadcastNotificationJob;

class QueueMonthlyKickoffBroadcast
{
    public function handle(MonthlyKickoffBroadcastTriggered $event): void
    {
        $start   = $event->startDate;
        $end     = $event->endDate;
        $action  = $event->action;
        $gatewayType = $event->gatewayType;
        $traceId = $event->traceId;

        LogHelper::logInfo(
            null,
            [
                'start_date' => $start,
                'end_date'   => $end,
                'action'     => $action,
            ],
            200,
            'QueueMonthlyKickoffBroadcast listener triggered',
            $traceId
        );

        SendMonthlyKickOffBroadcastNotificationJob::dispatch(
            $start,
            $end,
            $action,
            $gatewayType,
            $traceId
        )->onQueue('notification');
    }
}
