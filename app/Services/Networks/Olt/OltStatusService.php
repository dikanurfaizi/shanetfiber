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
        $status = OltHelper::STATUS_INACTIVE;

        // Ambil mapping OID vendor
        $mapping = OidMapping::with('details')
            ->find($olt->oid_mapping_id);

        $sysNameOid = $mapping?->getOid('sysName');

        if (!$sysNameOid) {
            LogHelper::logWarning(null, [
                'olt_id' => $olt->id ?? null,
                'vendor_mapping_id' => $olt->oid_mapping_id,
                'reason' => 'sysName OID not configured'
            ], 422, 'Skip OLT status check: sysName OID is null');

            return [
                'status' => OltHelper::STATUS_INACTIVE,
                'duration' => 0,
            ];
        }

        $resolvedIp = SnmpHelper::resolveIp($olt->ip);
        if (!$resolvedIp) {
            LogHelper::logError(null, [
                'sysNameOid' => $sysNameOid,
                'resolvedIp' => $resolvedIp,
                'olt' => $olt
            ], 404, "IP resolution failed");
            return ['status' => $status, 'duration' => 0];
        }

        $session = SnmpCoreService::snmpGet($resolvedIp, $olt->port, $olt->community, $sysNameOid, $olt->version);

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
            'status'   => $session ? OltHelper::STATUS_ACTIVE : OltHelper::STATUS_INACTIVE,
            'duration' => $duration,
            'username' => $username,
        ], 200, "checkOltStatus Success");

        return [
            'status'   => $session ? OltHelper::STATUS_ACTIVE : OltHelper::STATUS_INACTIVE,
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
