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
        // dd($user);
        $roles = $user->roles->pluck('name');
        $isSuperAdmin = $roles->contains('Super Admin');
        $isAdmin = $roles->contains('Admin');
        $isCashier = $roles->contains('Kasir');
        $userId = $user->id;

        return [
            'user' => $user,
            'isSuperAdmin' => $isSuperAdmin,
            'isAdmin' => $isAdmin,
            'userId' => $userId,
            'role' => $roles,
            'isCashier' => $isCashier,
        ];
    }

    /**
     * Mengecek apakah pengguna yang sedang login adalah super admin.
     *
     * @return bool
     */
    public static function isSuperAdmin()
    {
        $userDetails = self::getUserDetails();
        return $userDetails['isSuperAdmin'];
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

    /**
     * Mengecek apakah pengguna yang sedang login adalah kasir.
     *
     * @return int
     */
    public static function isCashier()
    {
        $userDetails = self::getUserDetails();
        return $userDetails['isCashier'];
    }
}
