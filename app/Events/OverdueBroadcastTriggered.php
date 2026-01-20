<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OverdueBroadcastTriggered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $traceId
    ) {}
}
