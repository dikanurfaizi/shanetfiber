<?php

namespace App\Listeners\Telegram;

use App\DataTransferObjects\TelegramJobDto;
use App\Enums\TelegramAction;
use App\Events\SingleNotification\PackageChangesRequest;
use App\Services\Telegrams\TelegramMessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTelegramPackageChangesListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private TelegramMessageService $telegramMessageService,
    ) {}

    public function handle(PackageChangesRequest $event): void
    {
        $telegramJobData = new TelegramJobDto(
            customerId: $event->customer->id,
            action: TelegramAction::CHANGES_PACKAGE->value,
            traceId: $event->traceId,
            billingId: null,
            receiptId: null,
            chatId: null,
        );

        $this->telegramMessageService->process($telegramJobData, $event->traceId);
    }
}
