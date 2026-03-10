<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PayLaterApplied
{
    use Dispatchable;

    public function __construct(
        public int $billingId,
        public ?int $eventId,
        public string $traceId
    ) {}
}
