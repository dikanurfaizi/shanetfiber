<?php

namespace App\Events;

use App\Models\Customers\Customer;
use App\Models\Billings\Billing;
use App\Models\Billings\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public Billing $billing,
        public Order $order,
        public ?int $eventId = null,
        public string $traceId
    ) {}
}
