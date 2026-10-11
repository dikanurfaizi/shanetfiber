<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Events\SingleNotification\PayLaterRequest;
use App\Services\Notification\WhatsApp\Single\PaymentPayLaterNotificationService;

class SendPayLaterListener extends BaseWhatsAppListener
{
    protected WhatsAppAction $action = WhatsAppAction::PAYLATER;

    /**
     * Inject Service via Constructor
     */
    public function __construct(
        protected PaymentPayLaterNotificationService $service
    ) {}

    /**
     * Listener handle hanya menerima 1 argumen ($event)
     */
    public function handle(PayLaterRequest $event): void
    {
        $this->service->send(
            action: $this->action,
            billingId: $event->billingId,
            eventId: $event->eventId ?? null,
            traceId: $event->traceId
        );
    }
}
