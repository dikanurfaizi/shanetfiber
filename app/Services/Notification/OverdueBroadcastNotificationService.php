<?php

namespace App\Services\Notification;

use App\Helpers\LogHelper;
use App\Services\Whatsapp\CloudAPI\Broadcast\OverdueBroadcast;
use App\Services\Whatsapp\GowaService;

class OverdueBroadcastNotificationService
{
    public function __construct(
        private GowaService $gowaService,
        private OverdueBroadcast $overdueBroadcast
    ) {}

    public function run(string $traceId): void
    {
        try {
            LogHelper::logInfo(
                null,
                ['service' => 'OverdueBroadcastNotificationService'],
                200,
                'Starting overdue broadcast notifications',
                $traceId
            );

            // $this->gowaService->broadcastMidMonth($traceId);
            $this->overdueBroadcast->run($traceId);

            LogHelper::logInfo(
                null,
                ['service' => 'OverdueBroadcastNotificationService'],
                200,
                'Completed broadcast mid-month reminders',
                $traceId
            );
        } catch (\Throwable $e) {

            LogHelper::logError(
                $e,
                [
                    'service' => 'OverdueBroadcastNotificationService',
                    'error_message' => $e->getMessage(),
                ],
                500,
                'Failed executing overdue broadcast notifications',
                $traceId
            );
        }
    }
}
