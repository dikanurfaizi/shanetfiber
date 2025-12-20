<?php

namespace App\Jobs\Notification;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Customers\Customer;
use App\Services\Notification\PackageChangesNotificationService;

class SendPackageChangesNotificationJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public Customer $customer,
        public string $oldPackageName,
        public int $oldPackagePrice,
        public string $traceId
    ) {}

    public function handle(PackageChangesNotificationService $service): void
    {
        $service->send(
            customer: $this->customer,
            oldPackageName: $this->oldPackageName,
            oldPackagePrice: $this->oldPackagePrice,
            traceId: $this->traceId
        );
    }
}
