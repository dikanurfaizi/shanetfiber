<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class MonthlyKickoffBroadcastTriggered
{
    use Dispatchable, SerializesModels;

    public Carbon $startDate;
    public Carbon $endDate;
    public string $action;
    public string $traceId;

    public function __construct(Carbon $startDate, Carbon $endDate, string $action, string $traceId)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->action    = $action;
        $this->traceId   = $traceId;
    }
}
