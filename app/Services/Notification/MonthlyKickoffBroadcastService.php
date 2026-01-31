<?php

namespace App\Services\Notification;

use App\Helpers\LogHelper;
use App\Services\Whatsapp\GowaService;
use Illuminate\Support\Carbon;

class MonthlyKickoffBroadcastService
{
    public function __construct(
        private GowaService $gowaService,
    ) {}

    public function send(
        Carbon $startDate,
        Carbon $endDate,
        string $action,
        string $gatewayType,
        string $traceId
    ): void {
        try {
            $result = $this->gowaService->broadcastMonthly(
                $startDate,
                $endDate,
                $action,
                $gatewayType,
                $traceId
            );

            LogHelper::logInfo(
                null,
                [
                    'start_date'       => $startDate->toDateTimeString(),
                    'end_date'         => $endDate->toDateTimeString(),
                    'action'           => $action,
                    'gowa_result_type' => gettype($result),
                ],
                200,
                'Monthly kickoff broadcast sent successfully',
                $traceId
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                $e,
                [
                    'start_date' => $startDate->toDateTimeString(),
                    'end_date'   => $endDate->toDateTimeString(),
                    'action'     => $action,
                ],
                500,
                'Failed to send monthly kickoff broadcast',
                $traceId
            );

            throw $e;
        }
    }
}
