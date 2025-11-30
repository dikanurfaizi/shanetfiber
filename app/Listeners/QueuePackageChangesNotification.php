<?php

namespace App\Listeners;

use App\Events\PackageChangesApplied;
use App\Jobs\Notification\SendPackageChangesNotificationJob;

class QueuePackageChangesNotification
{
    public function handle(PackageChangesApplied $event): void
    {
        SendPackageChangesNotificationJob::dispatch(
            customer: $event->customer,
            oldPackageName: $event->oldPackageName,
            oldPackagePrice: $event->oldPackagePrice,
            traceId: $event->traceId
        )->onQueue('notification');
    }
}
