<?php

namespace App\Services\Radius;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Packages\PppProfile;
use Illuminate\Support\Facades\DB;
use Throwable;

class RadiusProfileService
{
    /**
     * Menyinkronkan satu profil PPP ke tabel radgroupreply FreeRADIUS.
     */
    public function syncProfile(PppProfile $profile, string $traceId = ''): bool
    {
        $timer = new ExecutionTimer();
        $groupName = $profile->profile_name;

        if ($profile->disabled === 'true') {
            // Jika profil non-aktif, hapus batasan dari radgroupreply
            return $this->deleteProfile($profile, $traceId);
        }

        try {
            $radiusDb = DB::connection('mysql_radius');

            // 1. Sinkronisasi Mikrotik-Rate-Limit
            if (!empty($profile->rate_limit)) {
                $radiusDb->table('radgroupreply')->updateOrInsert(
                    [
                        'groupname' => $groupName,
                        'attribute' => 'Mikrotik-Rate-Limit',
                    ],
                    [
                        'op'    => ':=',
                        'value' => $profile->rate_limit,
                    ]
                );
            }

            // 2. Sinkronisasi DNS Primary & Secondary
            if (!empty($profile->dns_server)) {
                $dnsServers = array_map('trim', explode(',', $profile->dns_server));

                if (isset($dnsServers[0]) && !empty($dnsServers[0])) {
                    $radiusDb->table('radgroupreply')->updateOrInsert(
                        [
                            'groupname' => $groupName,
                            'attribute' => 'MS-Primary-DNS',
                        ],
                        [
                            'op'    => ':=',
                            'value' => $dnsServers[0],
                        ]
                    );
                }

                if (isset($dnsServers[1]) && !empty($dnsServers[1])) {
                    $radiusDb->table('radgroupreply')->updateOrInsert(
                        [
                            'groupname' => $groupName,
                            'attribute' => 'MS-Secondary-DNS',
                        ],
                        [
                            'op'    => ':=',
                            'value' => $dnsServers[1],
                        ]
                    );
                }
            }

            LogHelper::logInfo('RadiusProfileService::syncProfile', [
                'profile_name' => $groupName,
                'rate_limit'   => $profile->rate_limit,
            ], 200, 'Successfully synced PPP profile to FreeRADIUS.', $traceId, $timer->lap());

            return true;
        } catch (Throwable $e) {
            LogHelper::logError('RadiusProfileService::syncProfile', [
                'profile_name' => $groupName,
                'exception'    => class_basename($e),
                'message'      => $e->getMessage(),
            ], 500, 'Failed to sync PPP profile to FreeRADIUS.', $traceId, $timer->lap());

            return false;
        }
    }

    /**
     * Menyinkronkan seluruh profil PPP aktif ke FreeRADIUS.
     */
    public function syncAllProfiles(string $traceId = ''): int
    {
        $profiles = PppProfile::where('disabled', 'false')->get();
        $count = 0;

        foreach ($profiles as $profile) {
            if ($this->syncProfile($profile, $traceId)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Menghapus atribut profil dari tabel radgroupreply FreeRADIUS.
     */
    public function deleteProfile(PppProfile $profile, string $traceId = ''): bool
    {
        $timer = new ExecutionTimer();
        $groupName = $profile->profile_name;

        try {
            DB::connection('mysql_radius')
                ->table('radgroupreply')
                ->where('groupname', $groupName)
                ->delete();

            LogHelper::logInfo('RadiusProfileService::deleteProfile', [
                'profile_name' => $groupName,
            ], 200, 'Successfully deleted PPP profile attributes from FreeRADIUS.', $traceId, $timer->lap());

            return true;
        } catch (Throwable $e) {
            LogHelper::logError('RadiusProfileService::deleteProfile', [
                'profile_name' => $groupName,
                'exception'    => class_basename($e),
                'message'      => $e->getMessage(),
            ], 500, 'Failed to delete PPP profile attributes from FreeRADIUS.', $traceId, $timer->lap());

            return false;
        }
    }
}
