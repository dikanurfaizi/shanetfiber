<?php

namespace App\Services\Networks;

use App\Models\Network\Olt;
use App\Models\Network\Ont;
use App\Models\Network\OltUpdateLog;
use App\Services\Networks\SnmpService;
use App\Helpers\SnmpHelper;
use App\Helpers\OltHelper;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class OltScanService
{
    public function startBulkScan(array $filters = []): string
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

    public function getScanProgress(string $jobId): array
    {
        $cacheKey = "scan_job_progress_{$jobId}";
        return Cache::get($cacheKey, ['status' => 'not_found']);
    }

    public function clearScanCache(string $jobId): void
    {
        $cacheKey = "scan_job_progress_{$jobId}";
        Cache::forget($cacheKey);
    }

    public function scanSingleOlt(int $oltId, string $username, string $traceId): array
    {
        return $this->performOltCheck($oltId, $username, true, 'OLT Scan', $traceId);
    }

    public function healthCheckSingleOlt(int $oltId, string $username, string $traceId): array
    {
        return $this->performOltCheck($oltId, $username, false, 'OLT Health Check', $traceId);
    }

    private function performOltCheck(int $oltId, string $username, bool $includeOnts, string $action, string $traceId): array
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

        $healthCheck = SnmpService::checkOltStatus($olt, $username, true);
        $status = SnmpHelper::mapSnmpStatus($healthCheck['status']);
        $totalOnts = 0;

        if ($status === 'Active' && $includeOnts) {
            $snmpData = SnmpService::getOnts($olt);
            $ontsData = $snmpData['onts'];

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
                SnmpService::updateOnts($olt, $ontsData, $snmpData['duration'], $username);
            } else {
                $olt->updateStatus(OltHelper::STATUS_INACTIVE);
                SnmpService::OltInsertLog(
                    $olt,
                    $isValidOnts
                        ? "OLT {$olt->ip} reachable tetapi data ONT tidak valid"
                        : "OLT {$olt->ip} tidak merespon SNMP request",
                    0,
                    0,
                    $healthCheck['status'],
                    $username,
                    $snmpData['duration'] ?? $duration ?? 0
                );
            }
            $totalOnts = Ont::where('olt_id', $oltId)->count();
        } else {
            $olt->updateStatus(OltHelper::STATUS_INACTIVE);
            $duration = round(microtime(true) - $startTime, 2);
            SnmpService::OltInsertLog($olt, "SNMP data invalid dari OLT {$olt->ip}", 0, 0, $healthCheck['status'], $username, $duration ?? 0);
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
