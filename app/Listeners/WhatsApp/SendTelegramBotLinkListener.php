<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Events\SingleNotification\TelegramBotLinkRequest;
use App\Services\Notification\WhatsApp\Single\TelegramBotLinkNotificationService;

class SendTelegramBotLinkListener extends BaseWhatsAppListener
{
    protected WhatsAppAction $action = WhatsAppAction::TELEGRAM_BOT;

    /**
     * Inject Service via Constructor
     */
    public function __construct(
        protected TelegramBotLinkNotificationService $service
    ) {}

    /**
     * Listener handle hanya menerima 1 argumen ($event)
     */

    public function handle(TelegramBotLinkRequest $event): void
    {
        $this->service->send(
            action: $this->action,
            receiverNumber: $event->receiverNumber,
            telegramGateway: $event->telegramGateway,
            eventId: $event->eventId,
            traceId: $event->traceId
        );
    }
}
