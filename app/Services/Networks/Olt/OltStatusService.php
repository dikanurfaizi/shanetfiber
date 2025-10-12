<?php

namespace App\Services\Networks\Olt;

use App\Models\Network\OidMapping;
use App\Models\Network\Olt;
use App\Helpers\SnmpHelper;
use App\Helpers\LogHelper;
use App\Helpers\OltHelper;
use App\Services\Networks\HouseKeepingLog;
use App\Services\Networks\Snmp\SnmpCoreService;

class OltStatusService
{
    public static function checkOltStatus($olt, $username, bool $updateDb = false)
    {
        $startTime = microtime(true);
        $status = 'Down';

        // Ambil mapping OID vendor
        $mapping = OidMapping::with('details')
            ->find($olt->oid_mapping_id);

        $sysNameOid = $mapping?->getOid('sysName');

        $resolvedIp = $sysNameOid ? SnmpHelper::resolveIp($olt->ip) : null;
        if (!$resolvedIp) {
            LogHelper::logError(null, [
                'sysNameOid' => $sysNameOid,
                'resolvedIp' => $resolvedIp,
                'olt' => $olt
            ], 404, "IP resolution failed");
            return ['status' => $status, 'duration' => 0];
        }

        $session = SnmpCoreService::snmpGet($olt->ip, $olt->port, $olt->community, $sysNameOid, $olt->version);

        OltLogService::houseKeep();

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        if ($updateDb && $olt instanceof Olt) {
            $olt->updateStatus($session ? OltHelper::STATUS_ACTIVE : OltHelper::STATUS_INACTIVE);
        }

        LogHelper::logInfo(null, [
            'resolvedIp' => $resolvedIp,
            'sysNameOid' => $sysNameOid,
            'version'    => $olt->version,
            'session'  => $session,
            'status'   => $session ? 'Up' : 'Down',
            'duration' => $duration,
            'username' => $username,
        ], 200, "checkOltStatus Success");

        return [
            'status'   => $session ? 'Up' : 'Down',
            'duration' => $duration,
        ];
    }

    public static function getSysDescr($olt)
    {
        $startTime = microtime(true);

        $mapping = OidMapping::with('details')->where('vendor', $olt->vendor)->first();
        $sysDescrOid = $mapping?->getOid('sysDescr');

        if (!$sysDescrOid) {
            LogHelper::logError(null, ['olt' => $olt], 404, "sysDescr OID not found");
            return null;
        }

        $resolvedIp = SnmpHelper::resolveIp($olt->ip);
        if (!$resolvedIp) {
            LogHelper::logError(null, ['olt' => $olt], 404, "IP resolution failed");
            return null;
        }

        $session = SnmpCoreService::snmpGet($olt->ip, $olt->port, $olt->community, $sysDescrOid, $olt->version);
        // $sysDescr = @\snmpget("{$resolvedIp}:{$olt->port}", $olt->community, $sysDescrOid, 1000000, 1);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        LogHelper::logInfo(null, [
            'olt'       => $olt,
            'sysDescr'  => $session,
            'duration'  => $duration
        ], 200, "Get SysDescr");

        return $session;
    }
}
