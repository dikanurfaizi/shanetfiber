<?php

namespace App\Listeners;

use App\Events\PaymentCancelled;
use App\Jobs\Notification\SendPaymentCancellationNotificationJob;

class QueuePaymentCancellationNotification
{
    public function handle(PaymentCancelled $event): void
    {
        SendPaymentCancellationNotificationJob::dispatch(
            $event->receiptId,
            $event->billingId,
            $event->receiptData,
            $event->traceId
        )->onQueue('notification');;
    }
}
