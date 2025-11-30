<?php

namespace App\Jobs\Whatsapp;

use App\Helpers\LogHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessIncomingMediaMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $payload,
        public string $traceId
    ) {
        $this->onQueue('whatsapp-webhook');
    }

    public function handle(): void
    {
        // TODO: update status pesan di DB, dsb
        LogHelper::logInfo(null, [
            'payload' => $this->payload,
        ], 200, '[Job] ProcessIncomingMediaMessageJob', $this->traceId);
    }
}
