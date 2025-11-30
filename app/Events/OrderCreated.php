<?php

namespace App\Events;

use App\Models\Customer;
use App\Models\Billing\Billing;
use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public Billing $billing,
        public Order $order,
        public string $traceId
    ) {}
}
