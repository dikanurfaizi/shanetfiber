<?php

namespace App\Helpers;

use App\Models\WhatsApp\WhatsAppGateway;
use Exception;

class GatewayHelper
{
    /**
     * Build HTTP client options + resolve active account + return base URL.
     */
    public function resolve(WhatsAppGateway $gateway): array
    {
        // Cari akun aktif
        $account = $gateway->accounts()->where('is_active', true)->first();

        if (! $account) {
            throw new Exception("No active WhatsApp account found for gateway: {$gateway->id}");
        }

        // Validasi base_url wajib untuk Gowa
        if ($gateway->type === 'onprem' && empty($account->base_url)) {
            throw new Exception('Base URL is required for Gowa (onprem) gateway account.');
        }

        // Build default HTTP options
        $options = [
            'verify' => false,
        ];

        // Basic Auth handling
        if ($account->auth_enabled) {
            if (! $account->basic_auth_username || ! $account->basic_auth_password) {
                throw new Exception('Basic Auth enabled but username/password is missing.');
            }

            $options['auth'] = [
                $account->basic_auth_username,
                decrypt($account->basic_auth_password),
            ];
        }

        return [
            'options' => $options,
            'base_url' => $account->base_url,
            'account' => $account,
        ];
    }
}
