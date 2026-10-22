<?php

namespace App\Events;

use App\Models\Billings\Billing;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Packages\PppProfile;
use App\Models\Receipt;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegisteredEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Customer $customer,
        public readonly ?PppProfile $pppProfile = null,
        public readonly ?int $customerMembershipId = null,
        public readonly CustomerSubscription $customerSubscription,
        public readonly Billing $billing,
        public readonly Receipt $receipt,
        public readonly ?int $eventId = null,
        public readonly string $traceId = '',
        public readonly string $driver = 'both' // Tambahkan properti ini
    ) {}
}
