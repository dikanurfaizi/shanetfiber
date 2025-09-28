<?php

namespace App\Services\Networks;

use App\Models\Network\OidMapping;
use App\Models\Network\OltUpdateLog;
use App\Models\Network\Onu;
use App\Models\Network\Olt;
use App\Helpers\SnmpHelper;
use App\Helpers\LogHelper;
use App\Services\Networks\HouseKeepingLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SnmpService
{
    public static function checkOltStatus($olt, $username)
    {
        $startTime = microtime(true);
        $status = 'Down';

        LogHelper::logInfo(null, ['olt' => $olt], 200, "Start checkOltStatus");

        // Ambil mapping OID vendor
        $mapping = OidMapping::with('details')->where('vendor', $olt->vendor)->first();
        $sysNameOid = $mapping?->getOid('sysName');

        $resolvedIp = $sysNameOid ? SnmpHelper::resolveIp($olt->ip) : null;
        if (!$resolvedIp) {
            LogHelper::logError(null, ['olt' => $olt], 404, "IP resolution failed");
            return ['status' => $status, 'duration' => 0];
        }

        $version = match ($olt->version) {
            'v1'  => 0,
            'v2c' => 1,
            'v3'  => 2,
            default => 1,
        };

        LogHelper::logInfo(null, [
            'resolvedIp' => $resolvedIp,
            'sysNameOid' => $sysNameOid,
            'version'    => $version
        ], 200, "SNMP parameters");

        // Panggil SNMP di global namespace
        $session = @\snmpget("{$resolvedIp}:{$olt->port}", $olt->community, $sysNameOid, 1000000, $version);

        HouseKeepingLog::houseKeepingLog();

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        LogHelper::logInfo(null, [
            'session'  => $session,
            'status'   => $session ? 'Up' : 'Down',
            'duration' => $duration,
            'username' => $username
        ], 200, "End checkOltStatus");

        if ($session) {
            $olt->updateStatus(Olt::STATUS_ACTIVE);
        } else {
            $olt->updateStatus(Olt::STATUS_INACTIVE);
        }

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

        $sysDescr = @\snmpget("{$resolvedIp}:{$olt->port}", $olt->community, $sysDescrOid, 1000000, 1);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        LogHelper::logInfo(null, [
            'olt'       => $olt,
            'sysDescr'  => $sysDescr,
            'duration'  => $duration
        ], 200, "Get SysDescr");

        return $sysDescr;
    }

    public static function getOnus($olt)
    {
        $startTime = microtime(true);
        LogHelper::logInfo(null, ['olt' => $olt], 200, "Start getOnus");

        $mapping = OidMapping::with(['details' => fn($q) => $q->where('query_type', 2)])
            ->where('vendor', $olt->vendor)
            ->first();

        if (!$mapping) {
            LogHelper::logError(null, ['olt' => $olt], 404, "Mapping not found");
            return ['snmpData' => false, 'duration' => 0, 'totalOnu' => 0];
        }

        $oids = [];
        foreach ($mapping->details as $detail) {
            $oids[$detail->oid_key] = $detail->oid_value;
        }
        LogHelper::logInfo(null, ['oids' => $oids], 200, "OID list prepared");

        $resolvedIp = SnmpHelper::resolveIp($olt->ip);
        $port       = $olt->port ?? 161;
        $target     = "{$resolvedIp}:{$port}";
        $community  = $olt->community;
        $version    = $olt->version;

        $snmpWalkFn = match ($version) {
            'v1'  => fn($t, $c, $o) => @\snmpwalk($t, $c, $o, 1000000, 2),
            'v2c' => fn($t, $c, $o) => @\snmp2_real_walk($t, $c, $o, 1000000, 2),
            default => null,
        };

        if (!$snmpWalkFn) {
            LogHelper::logError(null, ['version' => $version], 200, "SNMP Version not recognized");
            return ['snmpData' => false, 'duration' => 0, 'totalOnu' => 0];
        }

        $snmpData = [];
        foreach ($oids as $key => $oid) {
            $snmpData[$key] = $snmpWalkFn($target, $community, $oid);
            $count = count((array)$snmpData[$key]);
            LogHelper::logInfo(null, ['key' => $key, 'count' => $count], 200, "SNMP walk result");
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        LogHelper::logInfo(null, ['snmpData' => $snmpData], 200, "SNMP Data");

        return [
            'snmpData' => $snmpData,
            'duration' => $duration,
        ];
    }

    public static function updateOnus($olt, $snmpData, $duration, $username)
    {
        if (empty($snmpData['oid_name']) || empty($snmpData['txpower']) || empty($snmpData['rxpower'])) {
            self::OltInsertLog($olt, "SNMP walk gagal / tidak ada response dari OLT {$olt->ip}", 0, 1, 'Down', $username, $duration);
            LogHelper::logError(null, ['snmpData' => $snmpData, 'duration' => $duration], 500, "Missing required SNMP data");
            return false;
        }

        // Mapping nilai SNMP per index
        $maps = [];
        foreach ($snmpData as $key => $values) {
            $maps[$key] = [];
            foreach ((array)$values as $oidStr => $val) {
                if (!preg_match('/(\d+)$/', $oidStr, $m)) continue;
                $mapIndex = $m[1];

                $maps[$key][$mapIndex] = match ($key) {
                    'txpower', 'rxpower' => preg_match('/-?\d+(\.\d+)?/', $val, $n) ? $n[0] : 'N/A',
                    'status'             => preg_match('/INTEGER: (\d+)/', $val, $n) ? ($n[1] == 1 ? 'Up' : 'Down') : 'Unknown',
                    'macaddress'         => SnmpHelper::formatMacFromSnmp($val),
                    'temperature'        => preg_match('/-?\d+/', $val, $n) ? $n[0] . " °C" : 'N/A',
                    default              => $val,
                };
            }
        }

        // Hapus data lama
        Onu::where('olt_id', $olt->id)->delete();
        DB::statement('ALTER TABLE onus AUTO_INCREMENT = 1');

        $success = 0;
        $failed = 0;
        $fieldMap = [
            'oid_name'    => 'name',
            'txpower'     => 'tx_power',
            'rxpower'     => 'rx_power',
            'status'      => 'status',
            'macaddress'  => 'mac_address',
            'temperature' => 'temperature',
        ];

        foreach ($snmpData['oid_name'] as $oid => $nameVal) {
            if (!preg_match('/(\d+(\.\d+)*)$/', $oid, $m)) continue;
            $fullIndex = $m[1];
            $mapIndex = (int)substr(strrchr($fullIndex, "."), 1);
            $indexing = SnmpHelper::extractLastThreeFromOid($fullIndex);

            try {
                $onuData = ['olt_id' => $olt->id, 'onu_index' => $indexing, 'last_updated' => now()];
                foreach ($fieldMap as $key => $col) {
                    $onuData[$col] = $key === 'oid_name' ? SnmpHelper::parseHexStringToAscii($nameVal) : ($maps[$key][$mapIndex] ?? (in_array($key, ['status', 'macaddress']) ? 'Unknown' : 'N/A'));
                }
                Onu::create($onuData);
                $success++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        self::OltInsertLog($olt, "Update ONU selesai untuk OLT {$olt->ip}", $success, $failed, 'Up', $username, $duration);
        HouseKeepingLog::houseKeepingLog();

        return true;
    }

    public static function OltInsertLog($olt, $details, $success, $failed, $status, $username, $duration)
    {
        OltUpdateLog::create([
            'olt_id'        => $olt->id,
            'updated_at'    => now(),
            'success_count' => $success,
            'failed_count'  => $failed,
            'details'       => $details,
            'onu_count'     => ($status === 'Down') ? $failed : $success,
            'status'        => $status,
            'username'      => $username,
            'duration'      => $duration
        ]);
    }

    public function scanAll($jobId, $username): void
    {
        $olts = Olt::with('onus')->get();
        $total = $olts->count();
        $current = 0;
        $onlineOlts = 0;

        LogHelper::logInfo(null, [
            'total_olts' => $total,
            'username' => $username
        ], 200, "Memulai scan semua OLT", $jobId);

        foreach ($olts as $olt) {
            $current++;

            try {
                LogHelper::logInfo(null, [
                    'olt_id'   => $olt->id,
                    'olt_name' => $olt->name,
                    'ip'       => $olt->ip,
                    'port'     => $olt->port,
                    'status'   => $olt->display_status
                ], 200, "Mulai scan OLT", $jobId);

                $healthCheck = Self::checkOltStatus($olt, $username);

                LogHelper::logInfo(null, [
                    'allData' => $healthCheck,
                ], 200, "Response HealthCheck");

                if ($healthCheck['status'] === 'Up') {
                    $snmpData = Self::getOnus($olt);
                    LogHelper::logInfo(null, [
                        'allData' => $snmpData,
                        'data' => $snmpData['snmpData'],
                        'duration' => $snmpData['duration']
                    ], 200, "Response HealthCheck success");
                    $olt->updateStatus(Olt::STATUS_ACTIVE);
                    Self::updateOnus($olt, $snmpData['snmpData'], $snmpData['duration'], $username);
                    $onlineOlts++;
                } else {
                    $totalOnu = Onu::where('olt_Id', $olt->id)->count();
                    LogHelper::logInfo(null, [
                        'allData' => $healthCheck,
                        'data' => $healthCheck['status'],
                        'duration' => $healthCheck['duration']
                    ], 200, "Response HealthCheck failed");
                    $olt->updateStatus(Olt::STATUS_INACTIVE);
                    self::OltInsertLog($olt, "Update ONU selesai untuk OLT {$olt->ip}", 0, $totalOnu, 'Up', $username, $healthCheck['duration']);
                }

                $log = OltUpdateLog::where('olt_id', $olt->id)
                    ->latest('updated_at')
                    ->first();

                $lastUpdate = $log?->updated_at ? Carbon::parse($log->updated_at) : null;
                $totalOnus  = Onu::count();

                // Struktur sama persis seperti updateProgress
                $progressData = [
                    'current'      => $current,
                    'total'        => $total,
                    'onlineOlts'   => $onlineOlts,
                    'totalOnus'    => $totalOnus,
                    'status'       => SnmpHelper::mapSnmpStatus($healthCheck['status']),
                    'currentOlt'   => $olt->name,
                    'lastResult'   => [
                        'olt_id'        => $olt->id,
                        'status'        => SnmpHelper::mapSnmpStatus($healthCheck['status']),
                        'onu_count'     => $olt->onus->count(),
                        'last_scan'     => $lastUpdate?->toDateTimeString(),
                        'last_scan_time' => $lastUpdate?->format('H:i'),
                        'last_scan_date' => $lastUpdate?->format('d/m/y'),
                    ],
                ];

                // --- LogHelper pakai struktur sama ---
                LogHelper::logInfo(null, $progressData, 200, "Hasil scan OLT", $jobId);

                // --- Update progress ---
                $this->updateProgress($jobId, $progressData);
                HouseKeepingLog::houseKeepingLog();
            } catch (\Throwable $e) {
                LogHelper::logError(null, [
                    'olt_id'        => $olt->id,
                    'olt_name'      => $olt->name,
                    'error_message' => $e->getMessage(),
                ], 500, "Scan OLT gagal", $jobId);

                $this->updateProgress($jobId, [
                    'current'    => $current,
                    'total'      => $total,
                    'status'     => 'running',
                    'currentOlt' => $olt->name,
                    'oltStatus'  => 'Error',
                ]);
            }
        }

        // selesai
        LogHelper::logInfo(null, [
            'total_olts'  => $total,
            'online_olts' => $onlineOlts,
        ], 200, "Selesai scan semua OLT", $jobId);

        $this->updateProgress($jobId, [
            'current'    => $total,
            'total'      => $total,
            'status'     => 'done',
            'percent'    => 100,
            'currentOlt' => null,
        ]);
    }

    private function updateProgress(string $jobId, array $data): void
    {
        $percent = isset($data['current'], $data['total'])
            ? intval(($data['current'] / $data['total']) * 100)
            : ($data['percent'] ?? 0);

        $data['percent'] = $percent;

        Cache::put("scan_progress_{$jobId}", $data, 600);
    }
}
