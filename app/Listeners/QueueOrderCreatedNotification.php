<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\Notification\SendOrderCreatedNotificationJob;

class QueueOrderCreatedNotification
{
    public function handle(OrderCreated $event): void
    {
        SendOrderCreatedNotificationJob::dispatch(
            customer: $event->customer,
            billing: $event->billing,
            order: $event->order,
            eventId: $event->eventId,
            traceId: $event->traceId
        )->onQueue('notification');
    }
}
