<?php

namespace App\Services\Networks;

use App\Models\Network\OidMapping;
use App\Models\Network\OltUpdateLog;
use App\Models\Network\Ont;
use App\Models\Network\Olt;
use App\Helpers\SnmpHelper;
use App\Helpers\LogHelper;
use App\Helpers\OltHelper;
use App\Services\Networks\HouseKeepingLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class SnmpService
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

        $version = match ($olt->version) {
            'v1'  => 0,
            'v2c' => 1,
            'v3'  => 2,
            default => 1,
        };

        // Panggil SNMP di global namespace
        $session = @\snmpget("{$resolvedIp}:{$olt->port}", $olt->community, $sysNameOid, 1000000, $version);

        HouseKeepingLog::houseKeepingLog();

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        if ($updateDb && $olt instanceof Olt) {
            $olt->updateStatus($session ? OltHelper::STATUS_ACTIVE : OltHelper::STATUS_INACTIVE);
        }

        LogHelper::logInfo(null, [
            'resolvedIp' => $resolvedIp,
            'sysNameOid' => $sysNameOid,
            'version'    => $version,
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

    public static function getOnts($olt)
    {
        $startTime = microtime(true);

        $mapping = OidMapping::with(['details' => fn($q) => $q->where('query_type', 2)])
            ->find($olt->oid_mapping_id);

        if (!$mapping) {
            LogHelper::logError(null, ['mapping' => $mapping, 'olt' => $olt], 404, "Mapping not found");
            return ['snmpData' => false, 'duration' => 0, 'totalOnt' => 0];
        }

        $oids = [];
        foreach ($mapping->details as $detail) {
            $oids[$detail->oid_key] = trim($detail->oid_value, ' .');
        }
        // LogHelper::logInfo(null, ['oids_keys' => array_keys($oids)], 200, "OID list prepared");

        $resolvedIp = SnmpHelper::resolveIp($olt->ip);
        $target     = "{$resolvedIp}:" . ($olt->port ?? 161);
        $community  = $olt->community;
        $version    = $olt->version;

        $snmpWalkFn = match ($version) {
            'v1'  => fn($t, $c, $o) => @\snmpwalk($t, $c, $o, 1000000, 2),
            'v2c' => fn($t, $c, $o) => @\snmp2_real_walk($t, $c, $o, 1000000, 2),
            default => null,
        };

        if (!$snmpWalkFn) {
            LogHelper::logError(null, ['version' => $version], 200, "SNMP Version not recognized");
            return ['snmpData' => false, 'duration' => 0, 'totalOnt' => 0];
        }

        $rawSnmp   = [];
        $ontsList  = [];
        foreach ($oids as $key => $baseOid) {
            $walkRes = $snmpWalkFn($target, $community, $baseOid);
            $rawSnmp[$key] = $walkRes;
            $count = count((array)$walkRes);

            // // log jumlah hasil per OID (tidak semua isi)
            // LogHelper::logInfo(null, ['key' => $key, 'count' => $count], 200, "SNMP walk count");

            foreach ((array)$walkRes as $fullOid => $val) {
                if (is_array($val)) $val = reset($val);
                $index = self::extractIndex($fullOid, $baseOid);

                $clean = self::normalizeSnmpValue($val);
                if (!isset($ontsList[$index])) $ontsList[$index] = [];
                $ontsList[$index][$key] = $clean;
            }
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $totalOnt = count($ontsList);

        // Log sample saja biar ringkas
        LogHelper::logInfo(null, [
            'sample_onts' => array_slice($ontsList, 0, 5, true),
            'total_ont'   => $totalOnt
        ], 200, "ONT data sample");

        LogHelper::logDebug(null, ['rawSnmp' => $rawSnmp], "SNMP Raw Data (debug only)");

        return [
            'snmpData'  => $rawSnmp,
            'onts'      => $ontsList,
            'duration'  => $duration,
            'total_ont' => $totalOnt
        ];
    }

    private static function extractIndex($fullOid, $baseOid)
    {
        $base = ltrim($baseOid, '.');
        $full = ltrim($fullOid, '.');
        $prefix = $base . '.';
        if (str_starts_with($full, $prefix)) {
            return substr($full, strlen($prefix));
        }
        $parts = explode('.', $full);
        return implode('.', array_slice($parts, -3));
    }

    private static function normalizeSnmpValue($val)
    {
        if (!is_string($val)) return $val;

        if (preg_match('/Hex-STRING:\s*([0-9A-Fa-f\s]+)/', $val, $m)) {
            $hex = preg_replace('/\s+/', '', $m[1]);
            return strtoupper(implode(':', str_split($hex, 2)));
        }
        if (preg_match('/^[A-Z\-]+:\s*(.*)$/', $val, $m2)) {
            return trim($m2[1]);
        }
        return trim($val);
    }

    public static function updateOnts($olt, $snmpData, $duration, $username)
    {
        if (empty($snmpData) || !is_array($snmpData)) {
            self::OltInsertLog(
                $olt,
                "SNMP walk failed / not any response from OLT {$olt->ip}",
                0,
                0,
                'Down',
                $username,
                $duration
            );

            LogHelper::logError(null, [
                'olt_id' => $olt->id,
                'olt_ip' => $olt->ip,
                'message' => 'SNMP walk failed or empty data'
            ], 500, "Failed Update ONT");

            return false;
        }

        // Reset data lama
        Ont::where('olt_id', $olt->id)->delete();

        $successList = [];
        $failedList  = [];

        foreach ($snmpData as $index => $data) {
            try {
                $ontData = [
                    'olt_id'       => $olt->id,
                    'ont_index'    => $index,
                    'last_updated' => now(),
                    'name'         => SnmpHelper::parseHexStringToAscii($data['oid_name'] ?? ''),
                    'tx_power'     => trim($data['txpower'] ?? 'N/A', '"'),
                    'rx_power'     => trim($data['rxpower'] ?? 'N/A', '"'),
                    'status'       => ($data['status'] ?? '') == '1' ? 'Up' : 'Down',
                    'mac_address'  => SnmpHelper::formatMacFromSnmp($data['macaddress'] ?? ''),
                    'temperature' => isset($data['temperature'])
                        ? (int) round(floatval(trim($data['temperature'], '"'))) . " °C"
                        : 'N/A',
                ];

                Ont::create($ontData);
                $successList[] = $ontData;
            } catch (\Throwable $e) {
                $failedList[] = [
                    'index'   => $index,
                    'raw'     => $data,
                    'error'   => $e->getMessage()
                ];
            }
        }

        // Simpan log ke OltUpdateLog / table log internal
        self::OltInsertLog(
            $olt,
            "Update ONT selesai: {$olt->name} ({$olt->ip}:{$olt->port})",
            count($successList),
            count($failedList),
            'Up',
            $username,
            $duration
        );

        // Log summary lengkap sekali di akhir
        LogHelper::logInfo(null, [
            'olt_id'   => $olt->id,
            'olt_ip'   => $olt->ip,
            'total_candidates' => count($snmpData),
            'inserted' => count($successList),
            'failed'   => count($failedList),
            'failed_list'  => $failedList,
            'duration' => $duration,
        ], 200, "Summary insert ONT");

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
            'ont_count'     => ($status === 'Down') ? $failed : $success,
            'status'        => $status,
            'username'      => $username,
            'duration'      => $duration
        ]);
    }

    public function scanAll(string $jobId, string $username): void
    {
        $startTime = microtime(true);

        $olts = Olt::with('onts')->get();
        $total = $olts->count();
        $current = 0;
        $onlineOlts = 0;
        $totalOnts = 0;

        // LogHelper::logInfo(null, [
        //     'total_olts' => $total,
        //     'username'   => $username
        // ], 200, "Memulai scan semua OLT", $jobId);

        foreach ($olts as $olt) {
            $current++;

            try {
                LogHelper::logInfo(null, [
                    'olt_id'   => $olt->id,
                    'olt_name' => $olt->name,
                    'ip'       => $olt->ip,
                    'port'     => $olt->port,
                    'status_in_db'   => OltHelper::getDisplayStatus($olt->status)
                ], 200, "ScanAll Started", $jobId);

                $healthCheck = Self::checkOltStatus($olt, $username);

                if (empty($healthCheck)) {
                    LogHelper::logWarning(null, [
                        'olt_id' => $olt->id,
                        'ip'     => $olt->ip,
                    ], 204, "HealthCheck not response", $jobId);

                    continue;
                }

                // LogHelper::logInfo(null, ['healthCheck' => $healthCheck], 200, "Response HealthCheck", $jobId);

                if ($healthCheck['status'] === 'Up') {
                    $snmpData = Self::getOnts($olt);
                    $ontsData = $snmpData['onts'] ?? [];

                    // validasi OLT
                    $isValidOnts = is_array($ontsData) && !empty($ontsData);
                    if ($isValidOnts) {
                        $firstOnt = reset($ontsData);
                        $requiredKeys = ['oid_name', 'txpower', 'rxpower', 'status', 'macaddress', 'temperature'];
                        foreach ($requiredKeys as $k) {
                            if (!array_key_exists($k, $firstOnt)) {
                                $isValidOnts = false;
                                break;
                            }
                        }
                    }

                    if ($isValidOnts) {
                        $olt->updateStatus(OltHelper::STATUS_ACTIVE);
                        Self::updateOnts($olt, $ontsData, $snmpData['duration'], $username);
                        $totalOnts += $snmpData['total_ont'] ?? 0;
                        $onlineOlts++;
                    } else {
                        $olt->updateStatus(OltHelper::STATUS_INACTIVE);
                        self::OltInsertLog($olt, "SNMP data invalid dari OLT {$olt->ip}", 0, 0, 'Down', $username, $snmpData['duration'] ?? 0);
                    }
                }

                // ambil last scan log
                $log = OltUpdateLog::where('olt_id', $olt->id)->latest('updated_at')->first();
                $lastUpdate = $log?->updated_at ? Carbon::parse($log->updated_at) : null;

                $progressData = $this->buildProgressData(
                    $current,
                    $total,
                    $onlineOlts,
                    $totalOnts,
                    ($current < $total) ? 'queue' : 'done',
                    $olt->name,
                    [
                        'olt_id'         => $olt->id,
                        'status'         => SnmpHelper::mapSnmpStatus($healthCheck['status']),
                        'ont_count'      => $olt->onts->count(),
                        'last_scan'      => $lastUpdate?->toDateTimeString(),
                        'last_scan_time' => $lastUpdate?->format('H:i'),
                        'last_scan_date' => $lastUpdate?->format('d/m/y'),
                    ],
                    $duration = round(microtime(true) - $startTime, 2),
                    $jobId
                );

                $this->updateProgress($jobId, $progressData);
                HouseKeepingLog::houseKeepingLog();
            } catch (\Throwable $e) {
                $this->updateProgress($jobId, [
                    'current'    => $current,
                    'total'      => $total,
                    'status'     => 'running',
                    'currentOlt' => $olt->name,
                    'oltStatus'  => 'Error',
                ]);
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        LogHelper::logInfo(null, [
            'total_olts'   => $total,
            'online_olts'  => $onlineOlts,
            'duration_sec' => $duration,
        ], 200, "Selesai scan semua OLT", $jobId);
    }

    protected function updateProgress(string $jobId, array $progressData): void
    {
        $cacheKey = "scan_job_progress_{$jobId}";
        $cache = Cache::get($cacheKey, [
            'current' => 0,
            'total' => $progressData['total'] ?? 0,
            'percent' => 0,
            'status' => 'queued',
            'onlineOlts' => 0,
            'totalOnts' => 0,
            'olts' => [],
        ]);

        // Update data OLT tertentu
        if (isset($progressData['lastResult']['olt_id'])) {
            $oltId = $progressData['lastResult']['olt_id'];
            $cache['olts'][$oltId] = $progressData['lastResult'];
        }

        // Update summary
        $cache['current'] = $progressData['current'] ?? $cache['current'];
        $cache['total'] = $progressData['total'] ?? $cache['total'];
        $cache['percent'] = isset($cache['total']) && $cache['total'] > 0
            ? round(($cache['current'] / $cache['total']) * 100, 2)
            : 0;
        $cache['status'] = $progressData['status'] ?? $cache['status'];
        $cache['onlineOlts'] = $progressData['onlineOlts'] ?? $cache['onlineOlts'];
        $cache['totalOnts'] = $progressData['totalOnts'] ?? $cache['totalOnts'];

        Cache::put($cacheKey, $cache, now()->addMinutes(30));
    }

    protected function buildProgressData(
        int $current,
        int $total,
        int $onlineOlts,
        int $totalOnts,
        string $status,
        ?string $currentOlt,
        ?array $lastResult = null,
        ?float $duration = null,
        ?string $jobId = null // optional untuk trace log
    ): array {
        $progressData = [
            'current'      => $current,
            'total'        => $total,
            'percent' => ($total > 0) ? round(($current / $total) * 100, 2) : 0,
            'onlineOlts'   => $onlineOlts,
            'totalOnts'    => $totalOnts,
            'status'       => $status,
            'currentOlt'   => $currentOlt,
            'lastResult'   => $lastResult,
            'duration'     => $duration,
        ];

        // Logging komprehensif
        LogHelper::logInfo(
            null,
            $progressData,
            200,
            "Build progress data" . ($jobId ? " | jobId: $jobId" : ""),
            $jobId
        );

        return $progressData;
    }
}
