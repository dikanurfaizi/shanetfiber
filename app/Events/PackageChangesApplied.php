<?php

namespace App\Events;

use App\Models\Customers\Customer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackageChangesApplied
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public string $oldPackageName,
        public int $oldPackagePrice,
        public ?int $eventId = null,
        public string $traceId
    ) {}
}
