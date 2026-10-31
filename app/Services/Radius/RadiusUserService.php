<?php

namespace App\Services\Radius;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Mikrotik;
use App\Models\Packages\PppProfile;
use App\Models\Radius\RadCheck;
use App\Models\Radius\RadReply;
use App\Models\Radius\RadUserGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;
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

    /*
    |--------------------------------------------------------------------------
    | Reporting & Monitoring Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Total User per Paket / Group
     */
    public function getTotalUsersPerPackage(): Collection
    {
        return DB::connection('mysql_radius')
            ->table('radusergroup')
            ->select('groupname', DB::raw('COUNT(DISTINCT username) as total_users'))
            ->groupBy('groupname')
            ->get();
    }

    /**
     * Daftar User yang Sedang Memiliki Session Aktif
     */
    public function getActiveSessions(): Collection
    {
        return DB::connection('mysql_radius')
            ->table('radacct')
            ->select('username', 'nasipaddress', 'acctstarttime', 'acctsessiontime', 'acctinputoctets', 'acctoutputoctets')
            ->whereNull('acctstoptime')
            ->orderBy('acctstarttime', 'desc')
            ->get();
    }

    /**
     * Total Penggunaan Bandwidth per User (Hari Ini)
     */
    public function getTodayBandwidthUsagePerUser(): Collection
    {
        return DB::connection('mysql_radius')
            ->table('radacct')
            ->select(
                'username',
                DB::raw('SUM(acctinputoctets + acctoutputoctets) as total_bytes'),
                DB::raw('SUM(acctinputoctets + acctoutputoctets) / 1024 / 1024 as total_mb')
            )
            ->whereDate('acctstarttime', DB::raw('CURDATE()'))
            ->groupBy('username')
            ->orderBy('total_bytes', 'desc')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Network & Session Control (CoA / PoD) & NAS Management
    |--------------------------------------------------------------------------
    */

    /**
     * Putuskan Session Aktif User Menggunakan RADIUS PoD (Packet of Disconnect)
     *
     * @param string $username Nama user yang akan didisconnect
     * @param string $defaultNasIp IP Address fallback router/NAS (default: 127.0.0.1)
     * @param string $defaultSecret RADIUS Secret Key fallback (default: testing123)
     * @param int $defaultCoaPort Port CoA/PoD fallback pada NAS (default: 3799)
     * @param string|null $traceId
     * @param ExecutionTimer|null $timer
     * @return bool
     */
    public function disconnectUser(
        string $username,
        string $defaultNasIp = '127.0.0.1',
        string $defaultSecret = 'testing123',
        int $defaultCoaPort = 3799,
        ?string $traceId = null,
        ?ExecutionTimer $timer = null
    ): bool {
        $traceId = $traceId ?? (string) Str::uuid();
        $timer = $timer ?? new ExecutionTimer();

        LogHelper::logInfo(
            null,
            ['username' => $username],
            200,
            'Starting disconnect user.',
            $traceId,
            $timer->lap()
        );

        // 1. Cek apakah user sedang aktif di radacct
        $activeSession = DB::connection('mysql_radius')
            ->table('radacct')
            ->where('username', $username)
            ->whereNull('acctstoptime')
            ->first();

        // Jika tidak ada session aktif, return true (tidak ada yang perlu didisconnect)
        if (!$activeSession) {
            return true;
        }

        $nasIp = $activeSession->nasipaddress ?? $defaultNasIp;
        $framedIp = $activeSession->framedipaddress ?? null;
        $acctSessionId = $activeSession->acctsessionid ?? null;

        // 2. Ambil radius_secret & coa_port secara dinamis dari tabel mikrotiks berdasarkan NAS IP
        $mikrotik = Mikrotik::where('host', $nasIp)->first();
        $secret = (!empty($mikrotik?->radius_secret)) ? $mikrotik->radius_secret : $defaultSecret;
        $coaPort = (!empty($mikrotik?->coa_port)) ? (int) $mikrotik->coa_port : $defaultCoaPort;

        // 3. Susun payload atribut untuk radclient
        $attributes = "User-Name = \"{$username}\"\n";

        if ($acctSessionId) {
            $attributes .= "Acct-Session-Id = \"{$acctSessionId}\"\n";
        }

        if ($framedIp) {
            $attributes .= "Framed-IP-Address = {$framedIp}\n";
        }

        try {
            // Eksekusi radclient via Symfony Process
            $process = new Process([
                'radclient',
                '-x',
                "{$nasIp}:{$coaPort}",
                'disconnect',
                $secret
            ]);

            $process->setInput($attributes);
            $process->setTimeout(5); // timeout 5 detik
            $process->run();

            if ($process->isSuccessful()) {
                LogHelper::logInfo(
                    null,
                    [
                        'username' => $username,
                        'nas_ip'   => $nasIp,
                        'coa_port' => $coaPort
                    ],
                    200,
                    "CoA/PoD Disconnect sent successfully.",
                    $traceId,
                    $timer->lap()
                );
                return true;
            }

            LogHelper::logWarning(null, [
                'username' => $username,
                'nas_ip'   => $nasIp,
                'message'  => $process->getErrorOutput()
            ], 200, "CoA/PoD Failed", $traceId, $timer->lap());

            return false;
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'username' => $username,
                'nas_ip'   => $nasIp,
                'message'  => $e->getMessage()
            ], 200, "CoA/PoD Exception", $traceId, $timer->lap());
            return false;
        }
    }

    /**
     * Sync data MikroTik dari billing ke tabel 'nas' FreeRADIUS
     */
    public function syncNas(Mikrotik $mikrotik): void
    {
        // Jika router nonaktif, hapus dari daftar NAS RADIUS
        if ($mikrotik->disabled === 'true') {
            DB::connection('mysql_radius')
                ->table('nas')
                ->where('nasname', $mikrotik->host)
                ->delete();
            return;
        }

        // Upsert ke tabel 'nas' FreeRADIUS
        DB::connection('mysql_radius')
            ->table('nas')
            ->updateOrInsert(
                ['nasname' => $mikrotik->host], // Match berdasarkan IP Host MikroTik
                [
                    'shortname'   => $mikrotik->slug ?? $mikrotik->name,
                    'type'        => 'mikrotik',
                    'secret'      => $mikrotik->radius_secret ?? 'testing123',
                    'description' => "Router {$mikrotik->name} ({$mikrotik->merk_router})",
                ]
            );
    }
}
