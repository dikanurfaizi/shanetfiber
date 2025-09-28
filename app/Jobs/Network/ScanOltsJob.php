<?php

namespace App\Jobs\Network;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Networks\SnmpService;

class ScanOltsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jobId;
    protected $username;

    public function __construct($jobId, $username)
    {
        $this->jobId = $jobId;
        $this->username = $username;
    }

    public function handle(SnmpService $scanService): void
    {
        $scanService->scanAll($this->jobId, $this->username);
    }
}
