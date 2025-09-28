<?php

namespace App\Jobs\Network;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Networks\SnmpService;
use Illuminate\Support\Facades\Cache;
use App\Helpers\LogHelper;
use APp\Models\Network\Olt;

class CheckOltStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $olt;
    protected $username;

    public function __construct($olt, $username)
    {
        $this->olt = $olt;
        $this->username = $username;
    }

    public function handle()
    {
        $status = $this->checkStatusOlt($this->olt);

        // ambil semua status sebelumnya
        $allStatus = Cache::get('olt-status-all', []);

        // update status & simpan nama
        $allStatus[$this->olt->id] = [
            'status' => $status,
            'name' => $this->olt->name,
        ];

        Cache::put('olt-status-all', $allStatus, now()->addMinutes(5));

        // tentukan OLT selanjutnya yang masih Scanning
        $nextOltEntry = collect($allStatus)->first(fn($v) => $v['status'] === 'Scanning');
        $nextOltName = $nextOltEntry['name'] ?? null;

        LogHelper::logInfo(null, [
            'allStatus' => $allStatus,
            'nextOltEntry' => $nextOltEntry,
            'nextOltName' => $nextOltName
        ], 200, "Increment Status");

        // update progress
        $progress = [
            'current' => count(array_filter($allStatus, fn($v) => $v['status'] !== 'Scanning')),
            'total' => Cache::get('olt-status-total', count($allStatus)),
            'statuses' => $allStatus,
            'currentOltName' => $nextOltName,
        ];
        Cache::put('olt-status-progress', $progress, now()->addMinutes(5));
    }

    protected function checkStatusOlt($olt)
    {
        // implementasi SNMP sesuai kebutuhan
        // contoh: return 'active' atau 'inactive'
        $snmpStatus = SnmpService::checkOltStatus($this->olt, $this->username);
        $status = $this->mapSnmpStatus($snmpStatus['status']);
        Cache::put("olt-status-{$this->olt->id}", $status, now()->addMinutes(5));
        return $status;
    }

    protected function mapSnmpStatus(string $snmpStatus): string
    {
        return match ($snmpStatus) {
            'Up' => 'Active',
            'Down' => 'Inactive',
            default => 'Scanning',
        };
    }
}
