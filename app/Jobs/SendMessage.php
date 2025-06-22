<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\WhatsappMessageService;
use App\Services\TelegramMessageService;

class SendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private WhatsappMessageService $whatsappMessageService;
    private TelegramMessageService $telegramMessageService;

    public function __construct(
        private string $customerId,
        private string $action,
        private string $traceId
    ) {}

    public function handle(): void
    {
        $whatsappMessageService = app(WhatsappMessageService::class);
        $telegramMessageService = app(TelegramMessageService::class);

        $whatsappMessageService->activation($this->customerId, $this->action, $this->traceId);
        $telegramMessageService->activation($this->customerId, $this->action, $this->traceId);
    }
}
