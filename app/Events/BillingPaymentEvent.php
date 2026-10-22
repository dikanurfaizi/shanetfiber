<?php

namespace App\Events;

use App\Models\Billings\Billing;
use App\Models\Receipt;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillingPaymentEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Billing $billing,
        public readonly Receipt $receipt,
        public readonly ?int $eventId = null,
        public readonly string $traceId = '',
        public readonly string $driver
    ) {}
}
