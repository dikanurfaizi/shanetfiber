<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BroadcastMidMonthTriggered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $traceId
    ) {}
}
