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

class AddPaymentBeforeDueDateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mikrotik $mikrotik;
    private string $comment;
    private string $secretId;
    private SecretService $SecretService;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $mikrotik, string $comment, string $secretId)
    {
        $this->mikrotik = $mikrotik;
        $this->comment = $comment;
        $this->secretId = $secretId;
        $this->SecretService = app(SecretService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->SecretService->addCommentSecret($this->mikrotik, $this->comment, $this->secretId);
    }
}
