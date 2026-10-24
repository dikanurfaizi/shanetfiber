<?php

namespace App\Events\SingleNotification;

use App\Models\Billings\Billing;
use App\Models\Customers\CustomerSubscription;
use App\Models\Receipt;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivateCustomerEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ?int $customerMembershipId = null,
        public CustomerSubscription $customerSubscription,
        public Billing $billing,
        public Receipt $receipt,
        public ?int $eventId = null,
        public string $traceId
    ) {}
}
