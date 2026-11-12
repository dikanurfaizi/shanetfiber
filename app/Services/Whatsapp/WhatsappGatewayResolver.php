<?php

namespace App\Services\WhatsApp;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\WhatsApp\WhatsAppFeature;
use App\Models\WhatsApp\WhatsAppGateway;
use App\Models\WhatsApp\WhatsAppGatewayAccount;
use App\Models\WhatsApp\WhatsAppNumber;

class WhatsAppGatewayResolver
{
    public function resolveActiveGatewayAndAccount(string $alias, string $traceId): ?array
    {
        $timer = new ExecutionTimer();
        $feature = WhatsAppFeature::where('alias', $alias)->where('is_active', true)->first();

        if (!$feature) {
            LogHelper::logWarning('WhatsAppGatewayResolver', [
                'alias' => $alias,
            ], 400, 'No active WhatsApp feature found for alias: ' . $alias, $traceId, $timer->lap());

            return null;
        }

        $gateway = WhatsAppGateway::where('is_active', true)->where('type', $feature->type)->first();

        if (!$gateway) {
            LogHelper::logWarning('WhatsAppGatewayResolver', [
                'alias' => $alias,
                'feature_type' => $feature->type,
            ], 400, 'No active WhatsApp gateway found.', $traceId, $timer->lap());

            return null;
        }

        $account = WhatsAppGatewayAccount::where('whatsapp_gateway_id', $gateway->id)
            ->where('is_active', true)
            ->first();

        if (!$account) {
            LogHelper::logWarning('WhatsAppGatewayResolver', [
                'gateway_id' => $gateway->id,
            ], 400, 'No active WhatsApp gateway account found.', $traceId, $timer->lap());

            return null;
        }

        $number = WhatsAppNumber::where('whatsapp_gateway_account_id', $account->id)
            ->where('is_enabled', true)
            ->where('is_default', true)
            ->first();

        if (!$number) {
            LogHelper::logWarning('WhatsAppGatewayResolver', [
                'account_id' => $account->id,
            ], 400, 'No active WhatsApp number found.', $traceId, $timer->lap());

            return null;
        }

        LogHelper::logInfo('WhatsAppGatewayResolver', [
            'alias'      => $alias,
            'gateway_id' => $gateway->id,
            'account_id' => $account->id,
            'number_id'  => $number->id,
        ], 200, 'WhatsApp gateway and account resolved successfully.', $traceId, $timer->lap());

        return [$gateway, $account, $number];
    }
}
