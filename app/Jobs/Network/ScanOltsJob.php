<?php

namespace App\Jobs\Network;

use App\Helpers\LogHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\Networks\SnmpService;
use App\Models\Network\Olt;

class ScanOltsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected string $jobId;
    protected string $username;

    public function __construct(string $jobId, string $username)
    {
        $this->jobId = $jobId;
        $this->username = $username;
    }

    public function handle(SnmpService $scanService): void
    {
        LogHelper::logInfo(null, [
            'job_id' => $this->jobId,
            'username' => $this->username
        ], 200, "ScanOltsJob Started");
        $scanService->scanAll($this->jobId, $this->username);
    }
}
