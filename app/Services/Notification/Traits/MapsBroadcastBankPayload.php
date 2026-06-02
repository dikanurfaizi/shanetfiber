<?php

namespace App\Services\Notification\Traits;

use Illuminate\Support\Collection;
use App\Helpers\LogHelper;

trait MapsBroadcastBankPayload
{
    /**
     * Menyusun teks informasi bank & ewallet secara dinamis ke dalam satu payload string.
     */
    protected function resolveBankTemplateAndPayload(
        Collection $banks,
        Collection $ewallets,
        string $templateSlug
    ): array {

        $accountListText = "";
        $no = 1;

        // 1. Loop Daftar Bank
        foreach ($banks as $bank) {
            $accountListText .= "{$no}. *Bank {$bank->bank}*\n";
            $accountListText .= "   • No. Rekening: {$bank->account_number}\n";
            $accountListText .= "   • A.n: {$bank->name}\n\n";
            $no++;
        }

        // 2. Loop Daftar E-Wallet
        foreach ($ewallets as $wallet) {
            $accountListText .= "{$no}. *E-Wallet {$wallet->bank}*\n";
            $accountListText .= "   • Nomor: {$wallet->account_number}\n";
            $accountListText .= "   • A.n: {$wallet->name}\n\n";
            $no++;
        }

        // Hitung total channel pembayaran aktif
        $totalAccounts = $banks->count() + $ewallets->count();

        LogHelper::logInfo(
            null,
            [
                'total_banks'    => $banks->count(),
                'total_ewallets' => $ewallets->count(),
                'total_accounts' => $totalAccounts
            ],
            100,
            '[MapsBroadcastBankPayload] Generated dynamic account list text'
        );

        return [
            'template' => $templateSlug,
            'payload'  => [
                'account_total' => $totalAccounts,
                'account_list'  => rtrim($accountListText), // Menyimpan seluruh blok list rekening dinamis
            ]
        ];
    }
}
