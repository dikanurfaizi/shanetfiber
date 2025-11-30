<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $billingId,
        public ?int $receiptId,
        public ?array $receiptData,
        public string $traceId,
    ) {}
}
