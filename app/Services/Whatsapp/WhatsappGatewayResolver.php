<?php

namespace App\Services\Whatsapp;

use App\Helpers\LogHelper;
use App\Models\Whatsapp\WhatsappGateway;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use App\Models\Whatsapp\WhatsappNumber;

class WhatsappGatewayResolver
{
    public function resolveActiveGatewayAndAccount(string $traceId): ?array
    {
        $gateway = WhatsappGateway::where('is_active', true)->first();

        if (! $gateway) {
            LogHelper::logWarning(null, [], 400, 'No active WhatsApp gateway found.', $traceId);

            return null;
        }

        $account = WhatsappGatewayAccount::where('whatsapp_gateway_id', $gateway->id)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            LogHelper::logWarning(null, [], 400, 'No active WhatsApp gateway account found.', $traceId);

            return null;
        }

        $number = WhatsappNumber::where('whatsapp_gateway_account_id', $account->id)
            ->where('is_enabled', true)
            ->where('is_default', true)
            ->first();

        return [$gateway, $account, $number];
    }
}
