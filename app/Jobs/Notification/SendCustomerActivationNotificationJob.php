<?php

namespace App\Jobs\Notification;

use App\Models\Customer;
use App\Models\Billing\Billing;
use App\Models\Receipt;
use App\Services\Notification\CustomerActivationNotificationService;
use App\Helpers\LogHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCustomerActivationNotificationJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public Customer $customer,
        public Billing $billing,
        public Receipt $receipt,
        public string $traceId
    ) {}

    public function handle(CustomerActivationNotificationService $service): void
    {
        $service->send(
            customer: $this->customer,
            billing: $this->billing,
            receipt: $this->receipt,
            traceId: $this->traceId
        );
    }
}
