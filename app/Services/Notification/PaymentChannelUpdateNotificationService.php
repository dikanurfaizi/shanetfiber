<?php

namespace App\Services\Notification;

use App\Helpers\LogHelper;
use App\Services\Whatsapp\CloudAPI\Broadcast\PaymentChannelUpdate;

class PaymentChannelUpdateNotificationService
{
    public function __construct(
        private PaymentChannelUpdate $paymentChannelUpdate
    ) {}

    public function run(string $traceId): void
    {
        try {
            LogHelper::logInfo(
                null,
                ['service' => 'PaymentChannelUpdateNotificationService'],
                200,
                'Starting payment channel update notifications',
                $traceId
            );

            $this->paymentChannelUpdate->run($traceId);

            LogHelper::logInfo(
                null,
                ['service' => 'PaymentChannelUpdateNotificationService'],
                200,
                'Completed payment channel update notifications',
                $traceId
            );
        } catch (\Throwable $e) {

            LogHelper::logError(
                $e,
                [
                    'service' => 'PaymentChannelUpdateNotificationService',
                    'error_message' => $e->getMessage(),
                ],
                500,
                'Failed executing payment channel update notifications',
                $traceId
            );
        }
    }
}
