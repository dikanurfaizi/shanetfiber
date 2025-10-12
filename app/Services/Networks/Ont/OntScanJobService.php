<?php

namespace App\Services\Networks\Ont;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\Network\Olt;
use App\Models\Network\OltUpdateLog;
use App\Helpers\LogHelper;
use App\Helpers\OltHelper;
use App\Helpers\SnmpHelper;
use App\Services\Networks\Olt\OltLogService;
use App\Services\Networks\Olt\OltStatusService;
use App\Services\Networks\Ont\OntUpdateService;
use App\Services\Networks\HouseKeepingLog;
use App\Services\Networks\Snmp\SnmpFetchService;

class OntScanJobService
{
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

                $healthCheck = OltStatusService::checkOltStatus($olt, $username);

                if (empty($healthCheck)) {
                    LogHelper::logWarning(null, [
                        'olt_id' => $olt->id,
                        'ip'     => $olt->ip,
                    ], 204, "HealthCheck not response", $jobId);

                    continue;
                }

                // LogHelper::logInfo(null, ['healthCheck' => $healthCheck], 200, "Response HealthCheck", $jobId);

                if ($healthCheck['status'] === 'Up') {
                    $snmpData = SnmpFetchService::getOnts($olt);
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
                        OntUpdateService::updateOnts($olt, $ontsData, $snmpData['duration'], $username);
                        $totalOnts += $snmpData['total_ont'] ?? 0;
                        $onlineOlts++;
                    } else {
                        $olt->updateStatus(OltHelper::STATUS_INACTIVE);
                        $data = [
                            'olt_id'        => $olt->id,
                            'updated_at'    => now(),
                            'success_count' => 0,
                            'failed_count'  => 0,
                            'details'       => "SNMP data invalid dari OLT {$olt->ip}",
                            'ont_count'     => 0,
                            'status'        => 'Down',
                            'username'      => $username,
                            'duration'      => $snmpData['duration'] ?? 0
                        ];
                        OltLogService::insertLog($data);
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
                OltLogService::houseKeep();
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
