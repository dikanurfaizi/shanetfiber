<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Jobs\Notification\SendPaymentNotificationJob;

class QueuePaymentNotification
{
    public function handle(PaymentSucceeded $event): void
    {
        SendPaymentNotificationJob::dispatch(
            $event->receiptId,
            $event->traceId
        )->onQueue('notification');
    }
}
