<?php

namespace App\Services\Messaging\Collectors;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminBankCollector
{
    /**
     * Mengambil collection daftar rekening bank & ewallet aktif milik admin/user tertentu.
     */
    public function getBanksByUserId(int $userId): Collection
    {
        if ($userId <= 0) {
            return collect();
        }

        return DB::table('banks as b')
            ->join('bank_users as bu', 'b.id', '=', 'bu.bank_id')
            ->select([
                'b.id',
                'b.bank',
                'b.account_number',
                'b.name',
                'b.type',
                'b.is_active',
            ])
            ->where('bu.user_id', $userId)
            ->where('b.is_active', true)
            ->get();
    }

    /**
     * Mengambil informasi bank dalam bentuk string terformat siap pakai untuk template WhatsApp.
     */
    public function getFormattedBankInfo(int $userId, string $traceId): string
    {
        $timer = new ExecutionTimer();

        try {
            if ($userId <= 0) {
                LogHelper::logWarning(
                    null,
                    ['user_id' => $userId],
                    400,
                    '[AdminBankCollector] Invalid user_id provided',
                    $traceId,
                    $timer->lap()
                );
                return 'Tidak ada informasi bank';
            }

            $banks = $this->getBanksByUserId($userId);

            if ($banks->isEmpty()) {
                LogHelper::logWarning(
                    null,
                    ['user_id' => $userId],
                    404,
                    "[AdminBankCollector] No active bank accounts found for user_id={$userId}",
                    $traceId,
                    $timer->lap()
                );
                return 'Tidak ada informasi bank';
            }

            $formatted = $banks
                ->map(fn($b) => "- {$b->bank} - {$b->account_number} (a.n {$b->name})")
                ->implode("\n");

            // Log Info ringkas (hanya mencatat jumlah bank yang di-format)
            LogHelper::logInfo(
                null,
                [
                    'user_id'    => $userId,
                    'bank_count' => $banks->count(),
                ],
                200,
                '[AdminBankCollector] Bank information formatted successfully',
                $traceId,
                $timer->lap()
            );

            return $formatted;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                ['user_id' => $userId, 'error' => $e->getMessage(), 'e' => $e],
                is_int($e->getCode()) && $e->getCode() > 0 ? $e->getCode() : 500,
                "[AdminBankCollector] Failed to fetch/format bank data for user_id={$userId}",
                $traceId,
                $timer->lap()
            );

            return 'Terjadi kesalahan saat mengambil informasi bank';
        }
    }
}
