<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Events\SingleNotification\WhatsAppTestRequest;
use App\Services\Notification\WhatsApp\Single\WhatsAppTestMessageService;

class SendWhatsAppTestListener extends BaseWhatsAppListener
{
    protected WhatsAppAction $action = WhatsAppAction::TEST_MESSAGE;

    /**
     * Inject Service via Constructor
     */
    public function __construct(
        protected WhatsAppTestMessageService $service
    ) {}

    /**
     * Listener handle hanya menerima 1 argumen ($event)
     */
    public function handle(WhatsAppTestRequest $event): void
    {
        $this->service->send(
            $this->action,
            $event->receiverNumber,
            $event->traceId
        );
    }
}
