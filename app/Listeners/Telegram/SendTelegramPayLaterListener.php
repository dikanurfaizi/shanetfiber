<?php

namespace App\Listeners\Telegram;

use App\DataTransferObjects\TelegramJobDto;
use App\Enums\TelegramAction;
use App\Events\SingleNotification\PayLaterRequest;
use App\Services\Telegrams\TelegramMessageService;
use App\Models\Billings\Billing;

class SendTelegramPayLaterListener extends BaseTelegramListener
{
    public function __construct(
        private TelegramMessageService $telegramService
    ) {}

    public function handle(PayLaterRequest $event): void
    {
        $billing = Billing::find($event->billingId);
        if (!$billing) return;

        $telegramDto = new TelegramJobDto(
            customerId: $billing->customer_id,
            action: TelegramAction::PAYLATER_PAYMENT->value,
            traceId: $event->traceId,
            billingId: $event->billingId,
            receiptId: null,
            extra: [],
            chatId: null
        );

        $this->telegramService->process($telegramDto, $event->traceId);
    }
}
