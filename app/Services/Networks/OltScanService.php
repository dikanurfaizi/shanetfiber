<?php

namespace App\Services\Networks;

use App\Models\Network\Olt;
use App\Models\Network\Ont;
use App\Services\Networks\SnmpService;
use App\Helpers\SnmpHelper;
use App\Jobs\Network\ScanOltsJob;
use App\Jobs\Networks\CheckOltStatusJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

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

    public function initializeStatusCheck(): void
    {
        Cache::forget('olt-status-progress');
        Cache::forget('olt-status-all');
        Cache::forget('olt-status-total');

        $olts = Olt::all();
        $allStatus = $olts->mapWithKeys(fn($olt) => [
            $olt->id => ['status' => 'Scanning', 'name' => $olt->name]
        ])->toArray();

        Cache::put('olt-status-all', $allStatus, now()->addMinutes(5));
        Cache::put('olt-status-total', count($olts), now()->addMinutes(5));
        Cache::put('olt-status-progress', [
            'current' => 0,
            'total' => count($olts),
            'statuses' => $allStatus,
            'currentOltName' => $olts->first()->name ?? null
        ], now()->addMinutes(5));
    }

    public function getStatusCheckProgress(): array
    {
        return Cache::get('olt-status-progress', [
            'current' => 0,
            'total' => Olt::count(),
            'statuses' => [],
            'currentOltName' => null,
        ]);
    }

    public function scanSingleOlt(int $oltId, string $username): array
    {
        return $this->performOltCheck($oltId, $username, true, 'OLT Scan');
    }

    public function healthCheckSingleOlt(int $oltId, string $username): array
    {
        return $this->performOltCheck($oltId, $username, false, 'OLT Health Check');
    }

    private function performOltCheck(int $oltId, string $username, bool $includeOnts, string $action): array
    {
        $olt = Olt::find($oltId);

        if (!$olt) {
            throw new \InvalidArgumentException("OLT ID {$oltId} tidak ditemukan", 404);
        }

        $healthCheck = SnmpService::checkOltStatus($olt, $username, true);
        $status = SnmpHelper::mapSnmpStatus($healthCheck['status']);

        $response = [
            'success' => true,
            'olt_name' => $olt->name,
            'status' => $status,
            'message' => "Successfully get {$action}"
        ];

        if ($status['status'] === 'Up' && $includeOnts) {
            $snmpData = SnmpService::getOnts($olt);
            SnmpService::updateOnts($olt, $snmpData['snmpData'], $snmpData['duration'], $username);
            $response['total_ont'] = Ont::where('olt_id', $oltId)->count();
        }

        return $response;
    }
}
