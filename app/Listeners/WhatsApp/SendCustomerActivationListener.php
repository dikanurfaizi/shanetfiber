<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Events\SingleNotification\ActivateCustomerEvent;
use App\Events\UserRegisteredEvent;
use App\Services\Notification\WhatsApp\Single\CustomerActivationNotificationService;

class SendCustomerActivationListener extends BaseWhatsAppListener
{
    protected WhatsAppAction $action = WhatsAppAction::ACTIVATION;

    /**
     * Inject Service via Constructor
     */
    public function __construct(
        protected CustomerActivationNotificationService $service
    ) {}

    /**
     * Listener handle hanya menerima 1 argumen ($event)
     */
    public function handle(UserRegisteredEvent|ActivateCustomerEvent $event): void
    {
        $this->service->send(
            action: $this->action,
            customerMembershipId: $event->customerMembershipId,
            customerSubscription: $event->customerSubscription,
            billing: $event->billing,
            receipt: $event->receipt,
            eventId: $event->eventId,
            traceId: $event->traceId
        );
    }
}
