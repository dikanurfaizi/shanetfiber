<?php

namespace App\Services\Notification\WhatsApp;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\WhatsApp\WhatsAppGatewayResolver;

abstract class AbstractBaseNotificationService
{
    public function __construct(
        protected WhatsAppGatewayResolver $gatewayResolver
    ) {}

    /**
     * Helper universal untuk resolusi Active Gateway, Account, dan Number/Device ID.
     * Bisa digunakan oleh Broadcast Service maupun Single Notification Service.
     */
    protected function resolveGatewayContext(string $alias, string $traceId, ?ExecutionTimer $timer = null): ?array
    {
        $lap = $timer ? $timer->lap() : null;

        [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, $alias)
            ?? [null, null, null];

        if (!$gateway || !$account || !$number) {
            LogHelper::logWarning(
                null,
                [
                    'alias'            => $alias,
                    'resolved_gateway' => !empty($gateway),
                    'resolved_account' => !empty($account),
                    'resolved_number'  => !empty($number),
                ],
                404,
                "[GatewayResolver] Notification skipped: Gateway, account, or number could not be resolved for alias '{$alias}'",
                $traceId,
                $lap
            );

            return null;
        }

        return [
            'gateway'  => $gateway,
            'account'  => $account,
            'number'   => $number,
            'deviceId' => $number->device_id,
        ];
    }
}
