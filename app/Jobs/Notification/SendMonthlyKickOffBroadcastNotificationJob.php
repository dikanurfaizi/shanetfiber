<?php

namespace App\Jobs\Notification;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Services\Notification\MonthlyKickoffBroadcastService;
use App\Helpers\LogHelper;

class SendMonthlyKickOffBroadcastNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Carbon $startDate,
        public Carbon $endDate,
        public string $action,
        public string $traceId
    ) {}

    public function handle(MonthlyKickoffBroadcastService $service): void
    {
        LogHelper::logInfo(
            null,
            [
                'start_date' => $this->startDate,
                'end_date'   => $this->endDate,
                'action'     => $this->action,
            ],
            100,
            'SendMonthlyKickOffBroadcastNotificationJob started',
            $this->traceId
        );

        $service->send(
            $this->startDate,
            $this->endDate,
            $this->action,
            $this->traceId
        );

        LogHelper::logInfo(
            null,
            [
                'start_date' => $this->startDate,
                'end_date'   => $this->endDate,
                'action'     => $this->action,
            ],
            200,
            'SendMonthlyKickOffBroadcastNotificationJob finished',
            $this->traceId
        );
    }
}
