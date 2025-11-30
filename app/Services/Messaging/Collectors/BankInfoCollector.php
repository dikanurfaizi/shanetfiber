<?php

namespace App\Services\Messaging\Collectors;

use App\Models\Bank;
use App\Helpers\LogHelper;

class BankInfoCollector
{
    public function getBankInfo(int $userId): string
    {
        try {
            $banks = Bank::where('user_id', $userId)
                ->where('disabled', 'false')
                ->select('name', 'bank', 'account_number')
                ->get();

            if ($banks->isEmpty()) {
                LogHelper::logWarning(null, ['user_id' => $userId], 404, "No bank info found for user_id={$userId}");
                return 'Tidak ada informasi bank';
            }

            return $banks
                ->map(fn($b) => "- {$b->bank} - {$b->account_number} (a.n {$b->name})")
                ->implode("\n");
        } catch (\Throwable $e) {
            LogHelper::logError($e, ['user_id' => $userId], 500, "Gagal mengambil data bank");
            return 'Terjadi kesalahan saat mengambil informasi bank';
        }
    }
}
