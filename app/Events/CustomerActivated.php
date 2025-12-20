<?php

namespace App\Events;

use App\Models\Billings\Billing;
use App\Models\Receipt;
use App\Models\Customers\CustomerSubscription;
use App\Models\Membership\CustomerMembership;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerActivated
{
    use Dispatchable, SerializesModels;

    public ?int $customerMembershipId;
    public CustomerSubscription $customerSubscription;
    public Billing $billing;
    public Receipt $receipt;
    public string $traceId;

    public function __construct(
        ?int $customerMembershipId,
        CustomerSubscription $customerSubscription,
        Billing $billing,
        Receipt $receipt,
        string $traceId
    ) {
        $this->customerMembershipId = $customerMembershipId;
        $this->customerSubscription = $customerSubscription;
        $this->billing = $billing;
        $this->receipt = $receipt;
        $this->traceId = $traceId;
    }
}
