<?php

namespace App\Jobs;

use App\Models\Mikrotik;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Services\Routerboard\TestConnectionService;

class TestConnectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mikrotik $mikrotik;
    private string $profile;
    private string $comment;
    private string $secretId;
    private string $username;
    private TestConnectionService $testConnectionService;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $mikrotik)
    {
        $this->mikrotik = $mikrotik;
        $this->testConnectionService = app(TestConnectionService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->testConnectionService->checkOnline($this->mikrotik);
    }
}
