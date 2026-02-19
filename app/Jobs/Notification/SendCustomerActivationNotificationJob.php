<?php

namespace App\Jobs\Notification;

use App\Models\Membership\CustomerMembership;
use App\Models\Billings\Billing;
use App\Models\Customers\CustomerSubscription;
use App\Models\Receipt;
use App\Services\Notification\CustomerActivationNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCustomerActivationNotificationJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public ?int $customerMembershipId,
        public CustomerSubscription $customerSubscription,
        public Billing $billing,
        public Receipt $receipt,
        public ?int $eventId,
        public string $traceId
    ) {}

    public function handle(CustomerActivationNotificationService $service): void
    {
        $service->send(
            customerMembershipId: $this->customerMembershipId,
            customerSubscription: $this->customerSubscription,
            billing: $this->billing,
            receipt: $this->receipt,
            eventId: $this->eventId,
            traceId: $this->traceId
        );
    }
}
