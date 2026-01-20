<?php

namespace App\Listeners;

use App\Events\OverdueBroadcastTriggered;
use App\Jobs\Notification\SendOverdueBroadcastNotificationJob;

class QueueOverdueBroadcastNotification
{
    public function handle(OverdueBroadcastTriggered $event): void
    {
        SendOverdueBroadcastNotificationJob::dispatch(
            traceId: $event->traceId
        )->onQueue('notification');
    }
}
