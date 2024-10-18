<?php

namespace App\Jobs;

use App\Models\Mikrotik;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Services\Routerboard\ScheduleService;

class DisabledScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mikrotik $mikrotik;
    private string $scheduleId;
    private string $disabledStatus;
    private ScheduleService $ScheduleService;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $mikrotik, string $scheduleId, string $disabledStatus)
    {
        $this->mikrotik = $mikrotik;
        $this->scheduleId = $scheduleId;
        $this->disabledStatus = $disabledStatus;
        $this->ScheduleService = app(ScheduleService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->ScheduleService->disabledScheduleById($this->mikrotik, $this->scheduleId, $this->disabledStatus);
    }
}
