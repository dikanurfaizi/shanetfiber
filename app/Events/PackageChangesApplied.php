<?php

namespace App\Events;

use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackageChangesApplied
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public string $oldPackageName,
        public int $oldPackagePrice,
        public string $traceId
    ) {}
}
