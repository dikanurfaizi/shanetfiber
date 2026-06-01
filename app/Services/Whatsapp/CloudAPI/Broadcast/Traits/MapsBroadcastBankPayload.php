<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast\Traits;

use Illuminate\Support\Collection;
use App\Helpers\LogHelper;

trait MapsBroadcastBankPayload
{
    /**
     * Resolving template dan memetakan bank payload berdasarkan jumlah bank & e-wallet.
     */
    protected function resolveBankTemplateAndPayload(
        Collection $banks,
        Collection $ewallets,
        array $templates
    ): array {
        // Unpack nama template dari array config yang di-passing
        [$templateName1, $templateName2, $templateName3] = $templates;

        // Ambil baris data secara aman menggunakan method ->get() bawaan Eloquent Collection
        $b1 = $banks->get(0);
        $b2 = $banks->get(1);
        $w1 = $ewallets->get(0);

        // --- Tambahkan logging awal untuk debug jumlah data mentah ---
        LogHelper::logInfo(
            null,
            [
                'raw_banks_count'    => $banks->count(),
                'raw_ewallets_count' => $ewallets->count(),
                'resolved_bank_1'    => $b1 ? $b1->bank : 'null',
                'resolved_bank_2'    => $b2 ? $b2->bank : 'null',
                'resolved_wallet'    => $w1 ? $w1->bank : 'null'
            ],
            100,
            '[MapsBroadcastBankPayload] Resolving bank criteria'
        );

        if ($banks->count() >= 2 && $ewallets->count() >= 1) {
            // Kasus 1: 2 Bank, 1 Dana/E-wallet
            LogHelper::logInfo(null, ['selected_template' => $templateName1, 'case' => '2_banks_1_ewallet'], 100, '[MapsBroadcastBankPayload] Match Case 1');

            return [
                'template' => $templateName1,
                'payload' => [
                    'bank1_name'  => $b1->bank ?? '-',
                    'bank1_no'    => $b1->account_number ?? '-',
                    'bank1_an'    => $b1->name ?? '-',
                    'bank2_name'  => $b2->bank ?? '-',
                    'bank2_no'    => $b2->account_number ?? '-',
                    'bank2_an'    => $b2->name ?? '-',
                    'wallet_name' => $w1->bank ?? 'DANA',
                    'wallet_no'   => $w1->account_number ?? '-',
                    'wallet_an'   => $w1->name ?? '-',
                ]
            ];
        }

        if ($banks->count() == 1 && $ewallets->count() >= 1) {
            // Kasus 2: 1 Bank, 1 Dana/E-wallet
            LogHelper::logInfo(null, ['selected_template' => $templateName2, 'case' => '1_bank_1_ewallet'], 100, '[MapsBroadcastBankPayload] Match Case 2');

            return [
                'template' => $templateName2,
                'payload' => [
                    'bank1_name'  => $b1->bank ?? '-',
                    'bank1_no'    => $b1->account_number ?? '-',
                    'bank1_an'    => $b1->name ?? '-',
                    'wallet_name' => $w1->bank ?? 'DANA',
                    'wallet_no'   => $w1->account_number ?? '-',
                    'wallet_an'   => $w1->name ?? '-',
                ]
            ];
        }

        // Kasus 3: Hanya 2 Bank (Fallback jika e-wallet kosong)
        $fallbackB2 = $b2 ?? $b1;

        LogHelper::logInfo(
            null,
            [
                'selected_template' => $templateName3,
                'case'              => 'fallback_2_banks',
                'is_b2_fallback'    => empty($b2) ? 'YES' : 'NO'
            ],
            100,
            '[MapsBroadcastBankPayload] Match Case 3 (Fallback)'
        );

        return [
            'template' => $templateName3,
            'payload' => [
                'bank1_name' => $b1->bank ?? '-',
                'bank1_no'   => $b1->account_number ?? '-',
                'bank1_an'   => $b1->name ?? '-',
                'bank2_name' => $fallbackB2->bank ?? '-',
                'bank2_no'   => $fallbackB2->account_number ?? '-',
                'bank2_an'   => $fallbackB2->name ?? '-',
            ]
        ];
    }
}
