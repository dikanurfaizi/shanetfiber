<?php

namespace App\Events\Radius;

use App\Models\Customers\Customer;
use App\Models\Packages\PppProfile;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RadiusUserProvisionRequested
{
    use Dispatchable, SerializesModels;

    /**
     * @param Customer $customer Model Customer yang diaktifkan
     * @param PppProfile|null $pppProfile Model PPP Profile paketnya
     * @param string|null $traceId ID tracing untuk logging (opsional)
     */
    public function __construct(
        public Customer $customer,
        public ?PppProfile $pppProfile = null,
        public ?string $traceId = null
    ) {}
}
