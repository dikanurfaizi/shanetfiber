<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class MonthlyKickoffBroadcastTriggered
{
    use Dispatchable, SerializesModels;

    public string $action;
    public string $gatewayType;
    public string $traceId;

    public function __construct(string $action, string $gatewayType, string $traceId)
    {
        $this->action    = $action;
        $this->gatewayType = $gatewayType;
        $this->traceId   = $traceId;
    }
}
