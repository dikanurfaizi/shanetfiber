<?php

namespace App\Events\SingleNotification;

use App\Models\Billings\Billing;
use App\Models\Customers\CustomerSubscription;
use App\Models\Receipt;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerActivated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ?int $customerMembershipId,
        public readonly CustomerSubscription $customerSubscription,
        public readonly Billing $billing,
        public readonly Receipt $receipt,
        public readonly ?int $eventId,
        public readonly string $traceId
    ) {}
}
