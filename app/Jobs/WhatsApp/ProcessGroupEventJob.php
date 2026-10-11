<?php

namespace App\Jobs\WhatsApp;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessGroupEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $payload,
        public string $traceId
    ) {
        $this->onQueue('notification');
    }

    public function handle(): void
    {
        $timer = new ExecutionTimer();
        // TODO: update status pesan di DB, dsb
        LogHelper::logInfo(null, [
            'payload' => $this->payload,
        ], 200, '[Job] ProcessGroupEventJob', $this->traceId, $timer->lap());
    }
}
