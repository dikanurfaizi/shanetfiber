<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast\Traits;

use Illuminate\Support\Collection;
use App\Helpers\LogHelper;

trait MapsBroadcastBankPayload
{
    /**
     * Resolving template dan memetakan bank payload berdasarkan jumlah bank & e-walletsecara dinamis (mendukung 3 s.d 5 opsi template).
     */
    protected function resolveBankTemplateAndPayload(
        Collection $banks,
        Collection $ewallets,
        array $templates
    ): array {
        // Ambil baris data secara aman
        $b1 = $banks->get(0);
        $b2 = $banks->get(1);
        $w1 = $ewallets->get(0);

        $banksCount    = $banks->count();
        $ewalletsCount = $ewallets->count();

        // --- Tambahkan logging awal untuk debug jumlah data mentah ---
        LogHelper::logInfo(
            null,
            [
                'raw_banks_count'    => $banksCount,
                'raw_ewallets_count' => $ewalletsCount,
                'passed_templates'   => count($templates),
                'resolved_bank_1'    => $b1 ? $b1->bank : 'null',
                'resolved_bank_2'    => $b2 ? $b2->bank : 'null',
                'resolved_wallet'    => $w1 ? $w1->bank : 'null'
            ],
            100,
            '[MapsBroadcastBankPayload] Resolving bank criteria dynamically'
        );

        // =========================================================================
        // KONDISI 1: JIKA TEMPLATE JELAS DI-PASSING HINGGA 5 OPSI (Service Baru)
        // =========================================================================
        if (count($templates) >= 5) {
            [$temp2Rek1Wall, $temp1Rek1Wall, $temp2Rek, $temp1Rek, $temp1Wall] = $templates;

            // Kasus 1.1: Memiliki 2 Bank dan Minimal 1 E-Wallet
            if ($banksCount >= 2 && $ewalletsCount >= 1) {
                return $this->buildPayloadResponse($temp2Rek1Wall, '2_banks_1_ewallet', [
                    'bank1' => $b1,
                    'bank2' => $b2,
                    'wallet' => $w1
                ]);
            }

            // Kasus 1.2: Memiliki 1 Bank dan Minimal 1 E-Wallet
            if ($banksCount == 1 && $ewalletsCount >= 1) {
                return $this->buildPayloadResponse($temp1Rek1Wall, '1_bank_1_ewallet', [
                    'bank1' => $b1,
                    'wallet' => $w1
                ]);
            }

            // Kasus 1.3: Hanya memiliki 2 Bank (E-Wallet kosong tetapi bank melimpah)
            if ($banksCount >= 2 && $ewalletsCount == 0) {
                return $this->buildPayloadResponse($temp2Rek, '2_banks_only', [
                    'bank1' => $b1,
                    'bank2' => $b2
                ]);
            }

            // Kasus 1.4: Hanya memiliki 1 Bank saja
            if ($banksCount == 1 && $ewalletsCount == 0) {
                return $this->buildPayloadResponse($temp1Rek, '1_bank_only', [
                    'bank1' => $b1
                ]);
            }

            // Kasus 1.5: Hanya memiliki E-Wallet saja
            if ($banksCount == 0 && $ewalletsCount >= 1) {
                return $this->buildPayloadResponse($temp1Wall, '1_ewallet_only', [
                    'wallet' => $w1
                ]);
            }
        }

        // =========================================================================
        // KONDISI 2: FALLBACK BACKWARD COMPATIBILITY (Service Lama / Hanya 3 Template)
        // =========================================================================
        [$templateName1, $templateName2, $templateName3] = $templates;

        if ($banksCount >= 2 && $ewalletsCount >= 1) {
            return $this->buildPayloadResponse($templateName1, 'legacy_2_banks_1_ewallet', [
                'bank1' => $b1,
                'bank2' => $b2,
                'wallet' => $w1
            ]);
        }

        if ($banksCount == 1 && $ewalletsCount >= 1) {
            return $this->buildPayloadResponse($templateName2, 'legacy_1_bank_1_ewallet', [
                'bank1' => $b1,
                'wallet' => $w1
            ]);
        }

        // Fallback Kasus Terakhir service lama: Paksa mapping ke 2 bank (b2 duplikat b1 jika kosong)
        $fallbackB2 = $b2 ?? $b1;
        return $this->buildPayloadResponse($templateName3, 'legacy_fallback_2_banks', [
            'bank1' => $b1,
            'bank2' => $fallbackB2
        ]);
    }

    /**
     * Helper internal untuk standardisasi log dan penyusunan struktur return payload.
     */
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
