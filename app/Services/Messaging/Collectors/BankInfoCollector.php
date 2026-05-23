<?php

namespace App\Services\Messaging\Collectors;

use App\Helpers\LogHelper;
use App\Models\User;

class BankInfoCollector
{
    public function getBankInfo(int $userId): string
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                LogHelper::logWarning(null, ['user_id' => $userId], 404, "User not found for user_id={$userId}");
                return 'Pengguna tidak ditemukan';
            }

            $banks = $user->banks()
                ->where('banks.disabled', 'false') // Gunakan boolean false (tanpa kutip) sesuai struktur MySQL Anda
                ->select('banks.name', 'banks.bank', 'banks.account_number') // Ambil kolom spesifik dari tabel banks
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
