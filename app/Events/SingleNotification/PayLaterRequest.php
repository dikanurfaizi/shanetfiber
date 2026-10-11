<?php

namespace App\Events\SingleNotification;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayLaterRequest
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $billingId,
        public ?int $eventId,
        public string $traceId
    ) {}
}
