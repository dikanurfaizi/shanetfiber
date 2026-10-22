<?php

namespace App\Events;

use App\Models\Billings\Billing;
use App\Models\Receipt;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UnpaymentBillingEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Billing $billing,
        public readonly ?int $receiptId = null,
        public readonly array $receiptData,
        public readonly string $traceId = '',
        public readonly ?int $eventId = null,
        public readonly string $driver
    ) {}
}
