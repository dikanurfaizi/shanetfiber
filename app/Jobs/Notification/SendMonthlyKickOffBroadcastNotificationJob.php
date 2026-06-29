<?php

namespace App\Jobs\Notification;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Services\Notification\MonthlyKickoffBroadcastService;

class SendMonthlyKickOffBroadcastNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $action,
        public string $gatewayType,
        public string $traceId
    ) {}

    public function handle(MonthlyKickoffBroadcastService $service): void
    {
        $service->run(
            $this->action,
            $this->gatewayType,
            $this->traceId
        );
    }
}
