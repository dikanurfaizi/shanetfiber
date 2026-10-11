<?php

namespace App\Events\SingleNotification;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSucceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $receiptId,
        public ?int $eventId,
        public string $traceId,
    ) {}
}
