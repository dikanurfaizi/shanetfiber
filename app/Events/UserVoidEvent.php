<?php

namespace App\Events;

use App\Models\Customers\Customer;
use App\Models\Packages\Package;
use App\Models\Packages\PppProfile;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserVoidEvent
{

    use Dispatchable, SerializesModels;

    /**
     * @param Customer $customer Model Customer yang akan di nonaktifkan
     * @param Package $package Model Package paket Expire
     * @param string|null $traceId ID tracing untuk logging (opsional)
     */

    public function __construct(
        public Customer $customer,
        public Package $package,
        public ?PppProfile $pppProfile = null,
        public string $driver = 'both',
        public ?string $traceId = null
    ) {}
}
