<?php

namespace App\Jobs;

use App\Models\Messaging\MessageEvent;
use App\Services\Event\ResendEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendResentMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $eventId,
        private string $traceId
    ) {}

    public function handle(ResendEventService $service): void
    {
        $event = MessageEvent::findOrFail($this->eventId);
        $service->handle($event, $this->traceId);
    }
}
