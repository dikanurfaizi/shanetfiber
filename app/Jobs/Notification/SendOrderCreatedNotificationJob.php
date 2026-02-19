<?php

namespace App\Jobs\Notification;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Customers\Customer;
use App\Models\Billings\Billing;
use App\Models\Billings\Order;
use App\Services\Notification\OrderCreatedNotificationService;

class SendOrderCreatedNotificationJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public Customer $customer,
        public Billing $billing,
        public Order $order,
        public ?int $eventId = null,
        public string $traceId
    ) {}

    public function handle(OrderCreatedNotificationService $service): void
    {
        $service->send(
            customer: $this->customer,
            billing: $this->billing,
            order: $this->order,
            eventId: $this->eventId,
            traceId: $this->traceId
        );
    }
}
