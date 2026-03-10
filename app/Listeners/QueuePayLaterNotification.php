<?php

namespace App\Listeners;

use App\Events\PayLaterApplied;
use App\Jobs\Notification\SendPayLaterNotificationJob;

class QueuePayLaterNotification
{
    public function handle(PayLaterApplied $event): void
    {
        SendPayLaterNotificationJob::dispatch(
            $event->billingId,
            $event->eventId,
            $event->traceId
        )->onQueue('notification');
    }
}
