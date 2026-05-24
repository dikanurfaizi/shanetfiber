<?php

namespace App\Listeners;

use App\Events\PaymentChannelUpdateTriggered;
use App\Jobs\Notification\SendPaymentChannelUpdateNotificationJob;

class QueuePaymentChannelUpdate
{
    public function handle(PaymentChannelUpdateTriggered $event): void
    {
        SendPaymentChannelUpdateNotificationJob::dispatch(
            traceId: $event->traceId
        )->onQueue('notification');
    }
}
