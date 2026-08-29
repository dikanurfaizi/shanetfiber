<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast\Traits;

use Illuminate\Support\Collection;
use App\Helpers\LogHelper;

trait MapsBroadcastBankPayload
{
    /**
     * Resolving template dan memetakan bank payload berdasarkan key associative.
     */
    protected function resolveBankTemplateAndPayload(
        Collection $banks,
        Collection $ewallets,
        array $templates
    ): array {
        $b1 = $banks->get(0);
        $b2 = $banks->get(1);
        $b3 = $banks->get(2);
        $w1 = $ewallets->get(0);

        $banksCount    = $banks->count();
        $ewalletsCount = $ewallets->count();

        LogHelper::logInfo(
            null,
            [
                'raw_banks_count'    => $banksCount,
                'raw_ewallets_count' => $ewalletsCount,
                'resolved_bank_1'    => $b1 ? $b1->bank : 'null',
                'resolved_bank_2'    => $b2 ? $b2->bank : 'null',
                'resolved_bank_3'    => $b3 ? $b3->bank : 'null',
                'resolved_wallet'    => $w1 ? $w1->bank : 'null'
            ],
            100,
            '[MapsBroadcastBankPayload] Resolving bank criteria dynamically'
        );

        // 1. Evaluasi 3 Bank + 1 E-Wallet
        if ($banksCount >= 3 && $ewalletsCount >= 1 && isset($templates['3_banks_1_ewallet'])) {
            return $this->buildPayloadResponse($templates['3_banks_1_ewallet'], '3_banks_1_ewallet', [
                'bank1' => $b1, 'bank2' => $b2, 'bank3' => $b3, 'wallet' => $w1
            ]);
        }

        // 2. Evaluasi 3 Bank Only
        if ($banksCount >= 3 && $ewalletsCount == 0 && isset($templates['3_banks_only'])) {
            return $this->buildPayloadResponse($templates['3_banks_only'], '3_banks_only', [
                'bank1' => $b1, 'bank2' => $b2, 'bank3' => $b3
            ]);
        }

        // 3. Evaluasi 2 Bank + 1 E-Wallet
        if ($banksCount == 2 && $ewalletsCount >= 1 && isset($templates['2_banks_1_ewallet'])) {
            return $this->buildPayloadResponse($templates['2_banks_1_ewallet'], '2_banks_1_ewallet', [
                'bank1' => $b1, 'bank2' => $b2, 'wallet' => $w1
            ]);
        }

        // 4. Evaluasi 2 Bank Only
        if ($banksCount == 2 && $ewalletsCount == 0 && isset($templates['2_banks_only'])) {
            return $this->buildPayloadResponse($templates['2_banks_only'], '2_banks_only', [
                'bank1' => $b1, 'bank2' => $b2
            ]);
        }

        // 5. Evaluasi 1 Bank + 1 E-Wallet
        if ($banksCount == 1 && $ewalletsCount >= 1 && isset($templates['1_bank_1_ewallet'])) {
            return $this->buildPayloadResponse($templates['1_bank_1_ewallet'], '1_bank_1_ewallet', [
                'bank1' => $b1, 'wallet' => $w1
            ]);
        }

        // 6. Evaluasi 1 Bank Only
        if ($banksCount == 1 && $ewalletsCount == 0 && isset($templates['1_bank_only'])) {
            return $this->buildPayloadResponse($templates['1_bank_only'], '1_bank_only', [
                'bank1' => $b1
            ]);
        }

        // 7. Evaluasi 1 E-Wallet Only
        if ($banksCount == 0 && $ewalletsCount >= 1 && isset($templates['1_ewallet_only'])) {
            return $this->buildPayloadResponse($templates['1_ewallet_only'], '1_ewallet_only', [
                'wallet' => $w1
            ]);
        }

        // Fallback Default jika tidak ada yang cocok
        $defaultTemplate = current($templates) ?: 'info_billing_default';
        return $this->buildPayloadResponse($defaultTemplate, 'fallback_default', [
            'bank1' => $b1, 'bank2' => $b2
        ]);
    }

    private function buildPayloadResponse(string $templateName, string $caseName, array $data): array
    {
        LogHelper::logInfo(null, ['selected_template' => $templateName, 'case' => $caseName], 100, '[MapsBroadcastBankPayload] Match Dynamic Case');

        $payload = [];

        if (array_key_exists('bank1', $data)) {
            $payload['bank1_name'] = $data['bank1']?->bank ?? '-';
            $payload['bank1_no']   = $data['bank1']?->account_number ?? '-';
            $payload['bank1_an']   = $data['bank1']?->name ?? '-';
        }

        if (array_key_exists('bank2', $data)) {
            $payload['bank2_name'] = $data['bank2']?->bank ?? '-';
            $payload['bank2_no']   = $data['bank2']?->account_number ?? '-';
            $payload['bank2_an']   = $data['bank2']?->name ?? '-';
        }

        if (array_key_exists('bank3', $data)) {
            $payload['bank3_name'] = $data['bank3']?->bank ?? '-';
            $payload['bank3_no']   = $data['bank3']?->account_number ?? '-';
            $payload['bank3_an']   = $data['bank3']?->name ?? '-';
        }

        if (array_key_exists('wallet', $data)) {
            $payload['wallet_name'] = $data['wallet']?->bank ?? 'DANA';
            $payload['wallet_no']   = $data['wallet']?->account_number ?? '-';
            $payload['wallet_an']   = $data['wallet']?->name ?? '-';
        }

        return [
            'template' => $templateName,
            'payload'  => $payload
        ];
    }
}