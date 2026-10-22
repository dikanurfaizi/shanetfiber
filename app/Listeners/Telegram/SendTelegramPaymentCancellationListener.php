<?php

namespace App\Listeners\Telegram;

use App\DataTransferObjects\TelegramJobDto;
use App\Enums\TelegramAction;
use App\Events\UnpaymentBillingEvent;
use App\Services\Telegrams\TelegramMessageService;

class SendTelegramPaymentCancellationListener extends BaseTelegramListener
{
    public function __construct(
        private TelegramMessageService $telegramMessageService
    ) {}

    public function handle(UnpaymentBillingEvent $event): void
    {
        $telegramDto = new TelegramJobDto(
            customerId: $event->billing->customer_id,
            action: TelegramAction::UNPAYMENT->value,
            traceId: $event->traceId,
            billingId: $event->billing->id,
            receiptId: $event->receiptId,
            extra: [],
            chatId: null
        );

        $this->telegramMessageService->process($telegramDto, $event->traceId);
    }
}
