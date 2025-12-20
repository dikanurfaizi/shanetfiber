<?php

namespace App\Listeners;

use App\Events\CustomerActivated;
use App\Helpers\LogHelper;
use App\Jobs\Notification\SendCustomerActivationNotificationJob;

class QueueCustomerActivationNotification
{
    public function handle(CustomerActivated $event): void
    {
        SendCustomerActivationNotificationJob::dispatch(
            customerMembershipId: $event->customerMembershipId,
            customerSubscription: $event->customerSubscription,
            billing: $event->billing,
            receipt: $event->receipt,
            traceId: $event->traceId
        )->onQueue('notification');
    }
}
