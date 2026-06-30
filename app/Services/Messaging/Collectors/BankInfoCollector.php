<?php

namespace App\Services\Messaging\Collectors;

use App\Helpers\LogHelper;

class BankInfoCollector
{
    // 💡 Injeksikan AdminBankCollector melalui Constructor
    public function __construct(
        private AdminBankCollector $adminBankCollector
    ) {}

    public function getBankInfo(int $userId): string
    {
        try {
            if ($userId <= 0) {
                return 'Tidak ada informasi bank';
            }

            // 💡 Panggil collector baru yang menggunakan query builder ke table pivot bank_users
            $banks = $this->adminBankCollector->getBanksByUserId($userId);

            if ($banks->isEmpty()) {
                LogHelper::logWarning(null, ['user_id' => $userId], 404, "No bank info found via AdminBankCollector for user_id={$userId}");
                return 'Tidak ada informasi bank';
            }

            // 💡 Format data bank dipisahkan dengan baris baru (\n) agar rapi di teks WhatsApp
            return $banks
                ->map(fn($b) => "- {$b->bank} - {$b->account_number} (a.n {$b->name})")
                ->implode("\n");
        } catch (\Throwable $e) {
            LogHelper::logError($e, ['user_id' => $userId], 500, "Gagal mengambil data bank pada BankInfoCollector");
            return 'Terjadi kesalahan saat mengambil informasi bank';
        }
    }
}
