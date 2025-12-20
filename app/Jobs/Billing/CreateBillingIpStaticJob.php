<?php

namespace App\Jobs\Billing;

use App\Models\Mikrotik;
use App\Services\Routerboard\IpStaticService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class CreateBillingIpStaticJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mikrotik $mikrotik;
    private string $comment;
    private string $simplequeId;
    private string $traceId;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $mikrotik, string $comment, string $simplequeId, string $traceId)
    {
        $this->mikrotik = $mikrotik;
        $this->comment = $comment;
        $this->simplequeId = $simplequeId;
        $this->traceId = $traceId;
    }

    /**
     * Execute the job.
     */
    public function handle(IpStaticService $IpStaticService): void
    {
        $IpStaticService->setCommentSimpleque($this->mikrotik, $this->comment, $this->simplequeId, $this->traceId);
        //  $this->SecretService->deleteActiveSecret($this->mikrotik, $this->username);
    }
}
