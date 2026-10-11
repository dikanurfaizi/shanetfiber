<?php

namespace App\Listeners\Telegram;

use App\DataTransferObjects\TelegramJobDto;
use App\Enums\TelegramAction;
use App\Events\SingleNotification\PaymentCancelled;
use App\Services\Telegrams\TelegramMessageService;
use App\Models\Billings\Billing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTelegramPaymentCancellationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private TelegramMessageService $telegramMessageService
    ) {}

    public function handle(PaymentCancelled $event): void
    {
        $billing = Billing::find($event->billingId);
        if (!$billing) return;

        $telegramDto = new TelegramJobDto(
            customerId: $billing->customer_id,
            action: TelegramAction::UNPAYMENT->value,
            traceId: $event->traceId,
            billingId: $event->billingId,
            receiptId: $event->receiptId,
            extra: [],
            chatId: null
        );

        $this->telegramMessageService->process($telegramDto, $event->traceId);
    }
}
