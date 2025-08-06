<?php

namespace App\Jobs;

use App\Models\Mikrotik;
use App\Services\Routerboard\IpStaticService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;


class CancelPaymentIpStaticBeforeDueDateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mikrotik $mikrotik;
    private string $comment;
    private string $simpleque_id;
    private IpStaticService $IpStaticService;
    private string $traceId;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $mikrotik, string $simpleque_id, string $comment, string $traceId)
    {
        $this->mikrotik = $mikrotik;
        $this->simpleque_id = $simpleque_id;
        $this->comment = $comment;
        $this->IpStaticService = app(IpStaticService::class);
        $this->traceId = $traceId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->IpStaticService->setCommentSimpleque($this->mikrotik, $this->simpleque_id, $this->comment, $this->traceId);
    }
}
