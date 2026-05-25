<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers\Traits;

trait HasPaymentChannelPayload
{
    /**
     * Helper: 2 Bank + 1 E-Wallet (Total 9 Elemen)
     */
    protected function getTwoBanksAndOneWallet(array $payload): array
    {
        return [
            $payload['bank1_name'] ?? '-',
            $payload['bank1_no'] ?? '-',
            $payload['bank1_an'] ?? '-',
            $payload['bank2_name'] ?? '-',
            $payload['bank2_no'] ?? '-',
            $payload['bank2_an'] ?? '-',
            $payload['wallet_name'] ?? 'DANA',
            $payload['wallet_no'] ?? '-',
            $payload['wallet_an'] ?? '-',
        ];
    }

    /**
     * Helper: 1 Bank + 1 E-Wallet (Total 6 Elemen)
     */
    protected function getOneBankAndOneWallet(array $payload): array
    {
        return [
            $payload['bank1_name'] ?? '-',
            $payload['bank1_no'] ?? '-',
            $payload['bank1_an'] ?? '-',
            $payload['wallet_name'] ?? 'DANA',
            $payload['wallet_no'] ?? '-',
            $payload['wallet_an'] ?? '-',
        ];
    }

    /**
     * Helper: Hanya 2 Bank (Total 6 Elemen)
     */
    protected function getTwoBanksOnly(array $payload): array
    {
        return [
            $payload['bank1_name'] ?? '-',
            $payload['bank1_no'] ?? '-',
            $payload['bank1_an'] ?? '-',
            $payload['bank2_name'] ?? '-',
            $payload['bank2_no'] ?? '-',
            $payload['bank2_an'] ?? '-',
        ];
    }
}
