<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class UserAuth
{
    /**
     * Mendapatkan detail pengguna yang sedang login.
     *
     * @return array
     */
    public static function getUserDetails()
    {
        $user = Auth::user();
        $userId = $user->id;
        $userName = $user->name;

        return [
            'user' => $user,
            'userId' => $userId,
            'userName' => $userName, // Tambahkan ini
        ];
    }
}
