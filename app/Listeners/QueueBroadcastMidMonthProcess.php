<?php

namespace App\Listeners;

use App\Events\BroadcastMidMonthTriggered;
use App\Jobs\Notification\SendBroadcastMidMonthNotificationJob;

class QueueBroadcastMidMonthProcess
{
    public function handle(BroadcastMidMonthTriggered $event): void
    {
        SendBroadcastMidMonthNotificationJob::dispatch(
            traceId: $event->traceId
        )->onQueue('notification');
    }
}
