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

class ActivationCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mikrotik $mikrotik;
    private string $profile;
    private string $comment;
    // private string $secretId;
    private string $serviceName;
    private string $username;
    private string $password;
    private SecretService $SecretService;
    private string $traceId;

    /**
     * Create a new job instance.
     */
    //public function __construct(Mikrotik $mikrotik, string $comment, string $secretId, string $username)
    public function __construct(Mikrotik $mikrotik, string $username, string $password, string $serviceName, string $profile, string $comment, string $traceId)
    {
        $this->mikrotik = $mikrotik;
        $this->profile = $profile;
        $this->comment = $comment;
        // $this->secretId = $secretId;
        $this->serviceName = $serviceName;
        $this->username = $username;
        $this->password = $password;
        $this->SecretService = app(SecretService::class);
        $this->traceId = $traceId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // $this->SecretService->deleteActiveSecret($this->mikrotik, $this->username);
        // $this->SecretService->updateProfileSecret($this->mikrotik, $this->profile, $this->secretId);
        // $this->SecretService->addCommentSecret($this->mikrotik, $this->comment, $this->secretId);
        // $this->SecretService->enableSecret($this->mikrotik, $this->username);
        $createSecret = $this->SecretService->createSecret($this->mikrotik, $this->username, $this->password, $this->serviceName, $this->profile, $this->comment, $this->traceId);
    }
}
