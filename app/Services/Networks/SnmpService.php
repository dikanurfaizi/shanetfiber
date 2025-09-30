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
    public static function checkOltStatus($olt, $username, bool $updateDb = false)
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

        if ($updateDb && $olt instanceof Olt) {
            $olt->updateStatus($session ? Olt::STATUS_ACTIVE : Olt::STATUS_INACTIVE);
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
        LogHelper::logInfo(null, ['ip' => $olt->ip], 200, "Start getOnus");

        $mapping = OidMapping::with(['details' => fn($q) => $q->where('query_type', 2)])
            ->where('vendor', $olt->vendor)
            ->first();

        if (!$mapping) {
            LogHelper::logError(null, ['olt' => $olt], 404, "Mapping not found");
            return ['snmpData' => false, 'duration' => 0, 'totalOnu' => 0];
        }

        $oids = [];
        foreach ($mapping->details as $detail) {
            $oids[$detail->oid_key] = trim($detail->oid_value, ' .');
        }
        LogHelper::logInfo(null, ['oids_keys' => array_keys($oids)], 200, "OID list prepared");

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
            return ['snmpData' => false, 'duration' => 0, 'totalOnu' => 0];
        }

        $rawSnmp   = [];
        $onusList  = [];
        foreach ($oids as $key => $baseOid) {
            $walkRes = $snmpWalkFn($target, $community, $baseOid);
            $rawSnmp[$key] = $walkRes;
            $count = count((array)$walkRes);

            // log jumlah hasil per OID (tidak semua isi)
            LogHelper::logInfo(null, ['key' => $key, 'count' => $count], 200, "SNMP walk count");

            foreach ((array)$walkRes as $fullOid => $val) {
                if (is_array($val)) $val = reset($val);
                $index = self::extractIndex($fullOid, $baseOid);

                $clean = self::normalizeSnmpValue($val);
                if (!isset($onusList[$index])) $onusList[$index] = [];
                $onusList[$index][$key] = $clean;
            }
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $totalOnu = count($onusList);

        // Log sample saja biar ringkas
        LogHelper::logInfo(null, [
            'sample_onus' => array_slice($onusList, 0, 5, true),
            'total_onu'   => $totalOnu
        ], 200, "ONU data sample");

        LogHelper::logDebug(null, ['rawSnmp' => $rawSnmp], "SNMP Raw Data (debug only)");

        return [
            'snmpData'  => $rawSnmp,
            'onus'      => $onusList,
            'duration'  => $duration,
            'total_onu' => $totalOnu
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

    public static function updateOnus($olt, $snmpData, $duration, $username)
    {
        if (empty($snmpData) || !is_array($snmpData)) {
            self::OltInsertLog(
                $olt,
                "SNMP walk gagal / tidak ada response dari OLT {$olt->ip}",
                0,
                1,
                'Down',
                $username,
                $duration
            );
            return false;
        }

        // Reset data lama
        Onu::where('olt_id', $olt->id)->delete();
        DB::statement('ALTER TABLE onus AUTO_INCREMENT = 1');
        LogHelper::logInfo(null, [
            'olt_id' => $olt->id,
            'olt_ip' => $olt->ip,
            'total_candidates' => count($snmpData)
        ], 200, "Reset data ONU sebelum insert baru");

        $success = 0;
        $failed  = 0;

        foreach ($snmpData as $index => $data) {
            try {
                $onuData = [
                    'olt_id'       => $olt->id,
                    'onu_index'    => $index,
                    'last_updated' => now(),
                    'name'         => SnmpHelper::parseHexStringToAscii($data['oid_name'] ?? ''),
                    'tx_power'     => trim($data['txpower'] ?? 'N/A', '"'),
                    'rx_power'     => trim($data['rxpower'] ?? 'N/A', '"'),
                    'status'       => ($data['status'] ?? '') == '1' ? 'Up' : 'Down',
                    'mac_address'  => SnmpHelper::formatMacFromSnmp($data['macaddress'] ?? ''),
                    'temperature'  => isset($data['temperature'])
                        ? trim($data['temperature'], '"') . " °C"
                        : 'N/A',
                ];

                // Debug detail per ONU (hanya muncul kalau APP_DEBUG=true)
                LogHelper::logDebug(null, [
                    'index'    => $index,
                    'raw'      => $data,
                    'prepared' => $onuData
                ], "Prepared ONU data");

                Onu::create($onuData);
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                LogHelper::logError(null, [
                    'index'   => $index,
                    'olt_id'  => $olt->id,
                    'message' => $e->getMessage(),
                ], 500, "Gagal insert ONU");
            }
        }

        self::OltInsertLog(
            $olt,
            "Update ONU selesai: {$olt->name} ({$olt->ip}:{$olt->port})",
            $success,
            $failed,
            'Up',
            $username,
            $duration
        );

        LogHelper::logInfo(null, [
            'olt_id'   => $olt->id,
            'inserted' => $success,
            'failed'   => $failed,
            'duration' => $duration,
        ], 200, "Summary insert ONU");

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
        $startTime = microtime(true);

        $olts = Olt::with('onus')->get();
        $total = $olts->count();
        $current = 0;
        $onlineOlts = 0;
        $totalOnus = 0;

        LogHelper::logInfo(null, [
            'total_olts' => $total,
            'username'   => $username
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

                if (empty($healthCheck)) {
                    LogHelper::logWarning(null, [
                        'olt_id' => $olt->id,
                        'ip'     => $olt->ip,
                    ], 204, "HealthCheck tidak ada response", $jobId);

                    continue;
                }

                LogHelper::logInfo(null, ['healthCheck' => $healthCheck], 200, "Response HealthCheck", $jobId);

                if ($healthCheck['status'] === 'Up') {
                    $snmpData = Self::getOnus($olt);

                    // --- Validasi isi onus ---
                    $onusData = $snmpData['onus'] ?? [];
                    LogHelper::logInfo(null, ['onus_data' => $onusData], 200, "SnmpData Onus", $jobId);

                    $isValidOnus = is_array($onusData) && !empty($onusData);
                    if ($isValidOnus) {
                        $firstOnu = reset($onusData);
                        $requiredKeys = ['oid_name', 'txpower', 'rxpower', 'status', 'macaddress', 'temperature'];
                        foreach ($requiredKeys as $k) {
                            if (!array_key_exists($k, $firstOnu)) {
                                $isValidOnus = false;
                                break;
                            }
                        }
                    }

                    if ($isValidOnus) {
                        LogHelper::logInfo(null, [
                            'allData'  => $snmpData,
                            'duration' => $snmpData['duration']
                        ], 200, "SNMP data valid, siap update", $jobId);

                        $olt->updateStatus(Olt::STATUS_ACTIVE);
                        Self::updateOnus($olt, $onusData, $snmpData['duration'], $username);
                        $totalOnus += $snmpData['total_onu'] ?? 0;
                        $onlineOlts++;
                    } else {
                        LogHelper::logError(null, [
                            'olt_id'  => $olt->id,
                            'ip'      => $olt->ip,
                            'snmpRes' => $snmpData,
                        ], 500, "SNMP data invalid / tidak sesuai format", $jobId);

                        $olt->updateStatus(Olt::STATUS_INACTIVE);
                        self::OltInsertLog($olt, "SNMP data invalid dari OLT {$olt->ip}", 0, 0, 'Down', $username, $snmpData['duration'] ?? 0);
                    }
                }

                $log = OltUpdateLog::where('olt_id', $olt->id)
                    ->latest('updated_at')
                    ->first();

                $lastUpdate = $log?->updated_at ? Carbon::parse($log->updated_at) : null;

                $progressData = $this->buildProgressData(
                    $current,
                    $total,
                    $onlineOlts,
                    $totalOnus,
                    ($current < $total) ? 'queue' : 'done',
                    $olt->name,
                    [
                        'olt_id'         => $olt->id,
                        'status'         => SnmpHelper::mapSnmpStatus($healthCheck['status']),
                        'onu_count'      => $olt->onus->count(),
                        'last_scan'      => $lastUpdate?->toDateTimeString(),
                        'last_scan_time' => $lastUpdate?->format('H:i'),
                        'last_scan_date' => $lastUpdate?->format('d/m/y'),
                    ]
                );

                LogHelper::logInfo(null, $progressData, 200, "Hasil scan OLT", $jobId);

                $this->updateProgress($jobId, $progressData);
                HouseKeepingLog::houseKeepingLog();
            } catch (\Throwable $e) {
                LogHelper::logError(null, [
                    'olt_id'        => $olt->id,
                    'olt_name'      => $olt->name,
                    'error_message' => $e->getMessage(),
                    'trace'         => $e->getTraceAsString(),
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

        $duration = round(microtime(true) - $startTime, 2);

        LogHelper::logInfo(null, [
            'total_olts'   => $total,
            'online_olts'  => $onlineOlts,
            'duration_sec' => $duration,
        ], 200, "Selesai scan semua OLT", $jobId);
    }

    protected function updateProgress($jobId, array $progressData): void
    {
        $cacheKey = "scan_job_progress_{$jobId}";

        // Ambil data lama biar bisa interpolasi (smooth increment)
        $last = Cache::get($cacheKey);

        if ($last && isset($last['current'], $progressData['current'])) {
            if ($progressData['current'] > $last['current'] + 1) {
                $progressData['current'] = $last['current'] + 1;
            }
        }

        if (isset($progressData['current'], $progressData['total']) && $progressData['total'] > 0) {
            $progressData['percent'] = round(($progressData['current'] / $progressData['total']) * 100, 2);
        }

        // 🔹 Log sebelum menyimpan cache
        LogHelper::logInfo(
            $jobId,
            [
                'cache_key' => $cacheKey,
                'before_cache' => $last,
                'after_cache' => $progressData,
            ],
            200,
            "Update progress cache"
        );

        Cache::put($cacheKey, $progressData, now()->addMinutes(30));
    }

    protected function buildProgressData(
        int $current,
        int $total,
        int $onlineOlts,
        int $totalOnus,
        string $status,
        ?string $currentOlt,
        ?array $lastResult = null,
        ?float $duration = null,
        ?string $jobId = null // optional untuk trace log
    ): array {
        $progressData = [
            'current'      => $current,
            'total'        => $total,
            'onlineOlts'   => $onlineOlts,
            'totalOnus'    => $totalOnus,
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
