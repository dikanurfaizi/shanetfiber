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
        $user = Auth::user(); // Ambil pengguna yang sedang login
        $roles = $user->roles->pluck('name'); // Ambil semua nama role
        $isAdmin = $roles->contains('Admin'); // Cek apakah salah satu role adalah Admin
        $userId = $user->id; // Ambil ID pengguna

        return [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'userId' => $userId,
            'role' => $roles,
        ];
    }

    /**
     * Mengecek apakah pengguna yang sedang login adalah admin.
     *
     * @return bool
     */
    public static function isAdmin()
    {
        $userDetails = self::getUserDetails();
        return $userDetails['isAdmin'];
    }

    /**
     * Mendapatkan ID pengguna yang sedang login.
     *
     * @return int
     */
    public static function getUserId()
    {
        $userDetails = self::getUserDetails();
        return $userDetails['userId'];
    }

    /**
     * Mendapatkan Role pengguna yang sedang login.
     *
     * @return int
     */
    public static function getRole()
    {
        $userDetails = self::getUserDetails();
        return $userDetails['role'];
    }
}
