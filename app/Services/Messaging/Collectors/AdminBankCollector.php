<?php

namespace App\Services\Messaging\Collectors;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminBankCollector
{
    /**
     * Mengambil daftar rekening bank & ewallet aktif milik admin tertentu
     */
    public function getBanksByUserId(int $userId): Collection
    {
        if ($userId <= 0) {
            return collect();
        }

        // Menggunakan Query Builder langsung ke table banks ter-join pivot bank_users
        return DB::table('banks as b')
            ->join('bank_users as bu', 'b.id', '=', 'bu.bank_id')
            ->select([
                'b.id',
                'b.bank',
                'b.account_number',
                'b.name',
                'b.type',
                'b.disabled'
            ])
            ->where('bu.user_id', $userId)
            ->where('b.disabled', 'false')
            ->get();
    }
}
