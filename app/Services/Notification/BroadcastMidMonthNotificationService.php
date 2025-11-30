<?php

namespace App\Services\Notification;

use App\Helpers\LogHelper;
use App\Services\Whatsapp\GowaService;

class BroadcastMidMonthNotificationService
{
    public function __construct(
        private GowaService $gowaService
    ) {}

    public function run(string $traceId): void
    {
        try {
            LogHelper::logInfo(
                null,
                ['service' => 'BroadcastMidMonthNotificationService'],
                200,
                'Starting broadcast mid-month reminders',
                $traceId
            );

            $this->gowaService->broadcastMidMonth($traceId);

            LogHelper::logInfo(
                null,
                ['service' => 'BroadcastMidMonthNotificationService'],
                200,
                'Completed broadcast mid-month reminders',
                $traceId
            );
        } catch (\Throwable $e) {

            LogHelper::logError(
                $e,
                [
                    'service' => 'BroadcastMidMonthNotificationService',
                    'error_message' => $e->getMessage(),
                ],
                500,
                'Failed executing broadcast mid-month reminders',
                $traceId
            );
        }
    }
}
