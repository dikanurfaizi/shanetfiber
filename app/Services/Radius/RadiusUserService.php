<?php

namespace App\Services\Radius;

use App\Models\Packages\PppProfile;
use App\Models\Radius\RadCheck;
use App\Models\Radius\RadReply;
use App\Models\Radius\RadUserGroup;
use Illuminate\Support\Facades\DB;
use Exception;

class RadiusUserService
{
    /**
     * Registrasi atau Update Pelanggan Baru di FreeRADIUS Database
     */
    public function createOrUpdateUser(
        string $username,
        string $password,
        ?PppProfile $profile = null,
        ?string $framedIp = null
    ): bool {
        DB::connection('mysql_radius')->beginTransaction();

        try {
            // 1. Password di radcheck
            RadCheck::updateOrCreate(
                ['username' => $username, 'attribute' => 'Cleartext-Password'],
                ['op' => ':=', 'value' => $password]
            );

            // 2. Map ke radusergroup (Group-based ACL)
            if ($profile) {
                RadUserGroup::updateOrCreate(
                    ['username' => $username],
                    ['groupname' => $profile->profile_name, 'priority' => 1]
                );
            }

            // 3. SATU-SATUNYA penulisan ke radreply: Framed-IP-Address (IP Stasis)
            if (!empty($framedIp)) {
                RadReply::updateOrCreate(
                    ['username' => $username, 'attribute' => 'Framed-IP-Address'],
                    ['op' => ':=', 'value' => $framedIp]
                );
            } else {
                RadReply::where('username', $username)
                    ->where('attribute', 'Framed-IP-Address')
                    ->delete();
            }

            DB::connection('mysql_radius')->commit();
            return true;
        } catch (Exception $e) {
            DB::connection('mysql_radius')->rollBack();
            throw $e;
        }
    }

    /**
     * Isolir Pelanggan (Pindahkan Group ke 'Expire' dan aktifkan Auth-Type := Reject)
     */
    public function isolateUser(string $username, string $expireProfileGroup = 'Expire'): bool
    {
        DB::connection('mysql_radius')->beginTransaction();

        try {
            // A. Tolak autentikasi di radcheck
            RadCheck::updateOrCreate(
                [
                    'username'  => $username,
                    'attribute' => 'Auth-Type',
                ],
                [
                    'op'    => ':=',
                    'value' => 'Reject',
                ]
            );

            // B. Pindahkan group ke profil Isolir / Expire
            RadUserGroup::updateOrCreate(
                [
                    'username' => $username,
                ],
                [
                    'groupname' => $expireProfileGroup,
                    'priority'  => 1,
                ]
            );

            DB::connection('mysql_radius')->commit();
            return true;
        } catch (Exception $e) {
            DB::connection('mysql_radius')->rollBack();
            return false;
        }
    }

    /**
     * Buka Isolir Pelanggan (Kembalikan ke profil normal & hapus Auth-Type Reject)
     */
    public function unisolateUser(string $username, ?PppProfile $normalProfile = null): bool
    {
        DB::connection('mysql_radius')->beginTransaction();

        try {
            // A. Hapus rule Auth-Type Reject dari radcheck
            RadCheck::where('username', $username)
                ->where('attribute', 'Auth-Type')
                ->delete();

            // B. Kembalikan group ke profil normal paket langganan
            if ($normalProfile) {
                RadUserGroup::updateOrCreate(
                    [
                        'username' => $username,
                    ],
                    [
                        'groupname' => $normalProfile->profile_name,
                        'priority'  => 1,
                    ]
                );
            }

            DB::connection('mysql_radius')->commit();
            return true;
        } catch (Exception $e) {
            DB::connection('mysql_radius')->rollBack();
            return false;
        }
    }

    /**
     * Hapus Seluruh Record User dari Database RADIUS (radcheck, radreply, radusergroup)
     */
    public function deleteUser(string $username): void
    {
        DB::connection('mysql_radius')->transaction(function () use ($username) {
            RadCheck::where('username', $username)->delete();
            RadReply::where('username', $username)->delete();
            RadUserGroup::where('username', $username)->delete();
        });
    }
}
