<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Events\SingleNotification\OrderCreated;
use App\Services\Notification\WhatsApp\Single\OrderCreatedNotificationService;

class SendOrderCreatedListener extends BaseWhatsAppListener
{
    protected WhatsAppAction $action = WhatsAppAction::PAYMENT_ONLINE;

    /**
     * Inject Service via Constructor
     */
    public function __construct(
        protected OrderCreatedNotificationService $service
    ) {}


    public function handle(OrderCreated $event): void
    {
        $this->service->send(
            action: $this->action,
            customer: $event->customer,
            billing: $event->billing,
            order: $event->order,
            eventId: $event->eventId,
            traceId: $event->traceId
        );
    }
}
