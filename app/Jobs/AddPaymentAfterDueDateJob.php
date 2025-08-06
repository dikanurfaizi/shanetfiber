<?php

namespace App\Jobs;

use App\Models\Mikrotik;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Services\Routerboard\Ppp\SecretService;

class AddPaymentAfterDueDateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mikrotik $mikrotik;
    private string $profile;
    private string $comment;
    private string $secretId;
    private string $username;
    private SecretService $SecretService;
    private string $traceId;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $mikrotik, string $profile, string $comment, string $secretId, string $username, string $traceId)
    {
        $this->mikrotik = $mikrotik;
        $this->profile = $profile;
        $this->comment = $comment;
        $this->secretId = $secretId;
        $this->username = $username;
        $this->SecretService = app(SecretService::class);
        $this->traceId = $traceId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->SecretService->deleteActiveSecret($this->mikrotik, $this->username, $this->traceId);
        $this->SecretService->updateProfileSecret($this->mikrotik, $this->profile, $this->secretId, $this->traceId);
        $this->SecretService->addCommentSecret($this->mikrotik, $this->comment, $this->secretId, $this->traceId);
    }
}
