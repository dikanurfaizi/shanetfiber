<?php

namespace App\Listeners\Telegram;

use App\DataTransferObjects\TelegramJobDto;
use App\Enums\TelegramAction;
use App\Events\SingleNotification\CustomerActivated;
use App\Services\Telegrams\TelegramMessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTelegramCustomerActivationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly TelegramMessageService $telegramMessageService
    ) {}

    public function handle(CustomerActivated $event): void
    {
        $telegramJobData = new TelegramJobDto(
            customerId: $event->customerSubscription->customer_id,
            action: TelegramAction::ACTIVATION->value,
            traceId: $event->traceId,
            billingId: $event->billing->id,
            receiptId: $event->receipt->id,
            chatId: null
        );

        $this->telegramMessageService->process($telegramJobData, $event->traceId);
    }
}
