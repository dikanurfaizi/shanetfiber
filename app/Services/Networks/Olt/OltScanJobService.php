<?php

namespace App\Services\Networks\Olt;

use App\Models\Network\Olt;
use App\Models\Network\Ont;
use App\Models\Network\OltUpdateLog;
use App\Helpers\SnmpHelper;
use App\Helpers\OltHelper;
use App\Helpers\LogHelper;
use App\Services\Networks\Ont\OntUpdateService;
use App\Services\Networks\Snmp\SnmpFetchService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class OltScanJobService
{
    public static function startBulkScan(array $filters = []): string
    {
        $query = Olt::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('ip', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['olt_type'])) {
            $query->where('vendor', $filters['olt_type']);
        }

        if (!empty($filters['status']) && $filters['status'] != "0") {
            $query->where('status', $filters['status']);
        }

        $total = $query->count();

        return $total;
    }

    public static function getScanProgress(string $jobId): array
    {
        $cacheKey = "scan_job_progress_{$jobId}";
        return Cache::get($cacheKey, ['status' => 'not_found']);
    }

    public static function clearScanCache(string $jobId): void
    {
        $cacheKey = "scan_job_progress_{$jobId}";
        Cache::forget($cacheKey);
    }

    public static function scanSingleOlt(int $oltId, string $username, string $traceId): array
    {
        return self::performOltCheck($oltId, $username, true, 'OLT Scan', $traceId);
    }

    public static function healthCheckSingleOlt(int $oltId, string $username, string $traceId): array
    {
        return self::performOltCheck($oltId, $username, false, 'OLT Health Check', $traceId);
    }

    private static function performOltCheck(int $oltId, string $username, bool $includeOnts, string $action, string $traceId): array
    {
        $startTime = microtime(true);
        $olt = Olt::find($oltId);

        if (!$olt) {
            LogHelper::logError(null, [
                'error_code' => '404',
                'body' => [
                    'status' => 'Not found',
                    'olt_id' => $oltId,
                    'olt_name' => null,
                    'last_scan_time' => null,
                    'last_scan_date' => null,
                    'total_ont' => 0,
                    'action' => $action
                ],
                'message' => 'Olt not found'
            ], 404, "Olt not found", $traceId);
            return [
                'error_code' => '404',
                'body' => [
                    'status' => 'Not found',
                    'olt_id' => $oltId,
                    'olt_name' => null,
                    'last_scan_time' => null,
                    'last_scan_date' => null,
                    'total_ont' => 0
                ],
                'message' => 'Olt not found'
            ];
        }

        $healthCheck = OltStatusService::checkOltStatus($olt, $username, true);
        $totalOnts = 0;

        switch ($healthCheck['status']) {

            case 0:
                $status = OltHelper::STRING_STATUS_INACTIVE;
                break;

            case 1:
                $status = OltHelper::STRING_STATUS_ACTIVE;
                break;

            default:
                $status = OltHelper::STRING_STATUS_SCANNING;
                break;
        }

        if ($healthCheck['status'] === OltHelper::STATUS_ACTIVE && $includeOnts) {
            $snmpData = SnmpFetchService::getOnts($olt);
            $ontsData = $snmpData['onts'];
            $olt->updateStatus(OltHelper::STATUS_ACTIVE);

            // validasi OLT
            $isValidOnts = is_array($ontsData) && !empty($ontsData);

            if ($isValidOnts) {
                $firstOnt = reset($ontsData);

                $requiredKeys = [
                    'oid_name',
                    'txpower',
                    'rxpower',
                    'status',
                    'macaddress',
                    'temperature',
                ];

                $missingKeys = [];

                foreach ($requiredKeys as $k) {
                    if (!array_key_exists($k, $firstOnt)) {
                        $missingKeys[] = $k;
                    }
                }

                if (!empty($missingKeys)) {
                    $isValidOnts = false;

                    LogHelper::logWarning(
                        null,
                        [
                            'olt_id'        => $olt->id ?? null,
                            'olt_ip'        => $olt->ip ?? null,
                            'ont_total'     => is_array($ontsData) ? count($ontsData) : 0,
                            'first_ont_keys' => is_array($firstOnt) ? array_keys($firstOnt) : null,
                            'missing_keys'  => $missingKeys,
                        ],
                        400,
                        '[ONT_SCAN_INVALID_STRUCTURE]'
                    );
                }
            } else {
                LogHelper::logWarning(
                    null,
                    [
                        'olt_id'   => $olt->id ?? null,
                        'olt_ip'   => $olt->ip ?? null,
                        'ont_type' => gettype($ontsData),
                        'ont_data' => is_array($ontsData) ? 'empty array' : null,
                    ],
                    500,
                    "[ONT_SCAN_EMPTY_DATA]"
                );
            }

            if ($isValidOnts) {
                OntUpdateService::updateOnts($olt, $ontsData, $snmpData['duration'], $username);
            } else {
                $data = [
                    'olt_id'        => $olt->id,
                    'updated_at'    => now(),
                    'success_count' => 0,
                    'failed_count'  => 0,
                    'details'       => $isValidOnts ? "OLT {$olt->ip} reachable tetapi data ONT tidak valid" : "OLT {$olt->ip} tidak merespon SNMP request",
                    'ont_count'     => 0,
                    'status'        => $healthCheck['status'],
                    'username'      => $username,
                    'duration'      => $snmpData['duration'] ?? $duration ?? 0
                ];
                OltLogService::insertLog($data);
            }
            $totalOnts = Ont::where('olt_id', $oltId)->count();
        } else {
            $olt->updateStatus(OltHelper::STATUS_INACTIVE);
            $duration = round(microtime(true) - $startTime, 2);
            $data = [
                'olt_id'        => $olt->id,
                'updated_at'    => now(),
                'success_count' => 0,
                'failed_count'  => 0,
                'details'       => "SNMP data invalid dari OLT {$olt->ip}",
                'ont_count'     => 0,
                'status'        => $healthCheck['status'],
                'username'      => $username,
                'duration'      => $duration ?? 0
            ];
            OltLogService::insertLog($data);
        }

        // ambil last scan log
        $log = OltUpdateLog::where('olt_id', $olt->id)->latest('updated_at')->first();
        $lastUpdate = $log?->updated_at ? Carbon::parse($log->updated_at) : null;

        LogHelper::logInfo(null, [
            'error_code' => '200',
            'body' => [
                'status' => $status,
                'olt_id' => $olt->id,
                'olt_name' => $olt->name,
                'last_scan_time' => $lastUpdate?->format('H:i'),
                'last_scan_date' => $lastUpdate?->format('d/m/y'),
                'total_ont' => $totalOnts,
                'action' => $action
            ],
            'message' => "Successfully to {$action}"
        ], 200, "Done process performOltCheck", $traceId);

        $response = [
            'error_code' => '200',
            'body' => [
                'status' => $status,
                'olt_id' => $olt->id,
                'olt_name' => $olt->name,
                'last_scan_time' => $lastUpdate?->format('H:i'),
                'last_scan_date' => $lastUpdate?->format('d/m/y'),
                'total_ont' => $totalOnts,
                'action' => $action
            ],
            'message' => "Successfully to {$action}"
        ];

        return $response;
    }
}
