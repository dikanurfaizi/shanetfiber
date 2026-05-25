<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast\Traits;

use Illuminate\Support\Collection;

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

        if ($banks->count() >= 2 && $ewallets->count() >= 1) {
            // Kasus 1: 2 Bank, 1 Dana/E-wallet
            return [
                'template' => $templateName1,
                'payload' => [
                    'bank1_name'  => $banks[0]->bank ?? '-',
                    'bank1_no'    => $banks[0]->account_number ?? '-',
                    'bank1_an'    => $banks[0]->name ?? '-',
                    'bank2_name'  => $banks[1]->bank ?? '-',
                    'bank2_no'    => $banks[1]->account_number ?? '-',
                    'bank2_an'    => $banks[1]->name ?? '-',
                    'wallet_name' => $ewallets[0]->bank ?? 'DANA',
                    'wallet_no'   => $ewallets[0]->account_number ?? '-',
                    'wallet_an'   => $ewallets[0]->name ?? '-',
                ]
            ];
        }

        if ($banks->count() == 1 && $ewallets->count() >= 1) {
            // Kasus 2: 1 Bank, 1 Dana/E-wallet
            return [
                'template' => $templateName2,
                'payload' => [
                    'bank1_name'  => $banks[0]->bank ?? '-',
                    'bank1_no'    => $banks[0]->account_number ?? '-',
                    'bank1_an'    => $banks[0]->name ?? '-',
                    'wallet_name' => $ewallets[0]->bank ?? 'DANA',
                    'wallet_no'   => $ewallets[0]->account_number ?? '-',
                    'wallet_an'   => $ewallets[0]->name ?? '-',
                ]
            ];
        }

        // Kasus 3: Hanya 2 Bank (Fallback)
        $b1 = $banks->get(0);
        $b2 = $banks->get(1) ?? $b1;

        return [
            'template' => $templateName3,
            'payload' => [
                'bank1_name' => $b1->bank ?? '-',
                'bank1_no'   => $b1->account_number ?? '-',
                'bank1_an'   => $b1->name ?? '-',
                'bank2_name' => $b2->bank ?? '-',
                'bank2_no'   => $b2->account_number ?? '-',
                'bank2_an'   => $b2->name ?? '-',
            ]
        ];
    }
}
