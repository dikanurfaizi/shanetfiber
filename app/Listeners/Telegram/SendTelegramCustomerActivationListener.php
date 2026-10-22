<?php

namespace App\Listeners\Telegram;

use App\DataTransferObjects\TelegramJobDto;
use App\Enums\TelegramAction;
use App\Events\UserRegisteredEvent;
use App\Services\Telegrams\TelegramMessageService;

class SendTelegramCustomerActivationListener extends BaseTelegramListener
{
    public function __construct(
        private readonly TelegramMessageService $telegramMessageService
    ) {}

    public function handle(UserRegisteredEvent $event): void
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
