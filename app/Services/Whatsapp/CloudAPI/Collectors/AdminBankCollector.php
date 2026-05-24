<?php

namespace App\Services\Whatsapp\CloudAPI\Collectors;

use App\Models\User;
use Illuminate\Support\Collection;

class AdminBankCollector
{
    /**
     * Ambil rekening bank yang di-assign ke User (Admin) tertentu lewat tabel pivot bank_users
     */
    public function getBanksByUserId(int $userId): Collection
    {
        $user = User::find($userId);

        if (!$user) {
            return collect();
        }

        // Menarik data bank terhubung yang statusnya aktif (jika ada flag is_active di tabel banks)
        return $user->banks()
            ->where('banks.disabled', 'false')
            ->orderBy('banks.id', 'asc')
            ->get();
    }
}
