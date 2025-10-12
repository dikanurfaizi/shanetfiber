<?php

namespace App\Services\Networks\Snmp;

use App\Helpers\SnmpHelper;
use App\Helpers\LogHelper;

class SnmpCoreService
{
    public static function snmpGet(string $ip, int $port, string $community, string $oid, string $version)
    {
        $resolvedIp = SnmpHelper::resolveIp($ip);
        if (!$resolvedIp) return null;

        $versionNum = self::mapVersion($version);

        return @\snmpget("{$resolvedIp}:{$port}", $community, $oid, 1000000, $versionNum);
    }

    /**
     * Universal SNMP walk function (supports v1, v2c, v3).
     */
    public static function snmpWalk(
        string $ip,
        int $port,
        string $community,
        string $oid,
        string $version,
        ?array $v3Params = null
    ) {
        $resolvedIp = SnmpHelper::resolveIp($ip);
        if (!$resolvedIp) return [];

        $versionNum = self::mapVersion($version);
        $target = "{$resolvedIp}:{$port}";

        switch ($versionNum) {
            case 0: // v1
                return @\snmpwalk($target, $community, $oid, 1000000, 2);

            case 1: // v2c
                return @\snmp2_real_walk($target, $community, $oid, 1000000, 2);

            case 2: // v3
                if (!$v3Params) {
                    LogHelper::logError(null, [
                        'ip' => $ip,
                        'oid' => $oid,
                    ], 400, "SNMPv3 requires security parameters");
                    return [];
                }

                return @\snmp3_real_walk(
                    $target,
                    $v3Params['security_name'] ?? 'authPrivUser',
                    $v3Params['security_level'] ?? 'authPriv',
                    $v3Params['auth_protocol'] ?? 'MD5',
                    $v3Params['auth_passphrase'] ?? 'public',
                    $v3Params['priv_protocol'] ?? 'DES',
                    $v3Params['priv_passphrase'] ?? 'private',
                    $oid,
                    1000000,
                    2
                );

            default:
                return [];
        }
    }

    public static function mapVersion(string $version): int
    {
        return match (strtolower($version)) {
            'v1'  => 0,
            'v2c' => 1,
            'v3'  => 2,
            default => 1,
        };
    }

    public static function isReachable($ip, int $port = 161): bool
    {
        $resolved = SnmpHelper::resolveIp($ip);
        if (!$resolved) return false;

        $conn = @fsockopen($resolved, $port, $errno, $errstr, 2);
        if ($conn) {
            fclose($conn);
            return true;
        }
        return false;
    }
}
