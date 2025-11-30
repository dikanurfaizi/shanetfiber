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

class CreateBillingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mikrotik $mikrotik;
    private string $comment;
    private string $secretId;
    private string $traceId;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $mikrotik, string $comment, string $secretId, string $traceId)
    {
        $this->mikrotik = $mikrotik;
        $this->comment = $comment;
        $this->secretId = $secretId;
        $this->traceId = $traceId;
    }

    /**
     * Execute the job.
     */
    public function handle(SecretService $SecretService): void
    {
        $SecretService->addCommentSecret($this->mikrotik, $this->comment, $this->secretId, $this->traceId);
        //  $this->SecretService->deleteActiveSecret($this->mikrotik, $this->username);
    }
}
