<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Events\BillingPaymentEvent;
use App\Services\Notification\WhatsApp\Single\PaymentNotificationService;

class SendPaymentSucceededListener extends BaseWhatsAppListener
{
    protected WhatsAppAction $action = WhatsAppAction::PAYMENT;

    /**
     * Inject Service via Constructor
     */
    public function __construct(
        protected PaymentNotificationService $service
    ) {}

    /**
     * Listener handle hanya menerima 1 argumen ($event)
     */
    public function handle(BillingPaymentEvent $event): void
    {
        $this->service->send(
            $this->action,
            $event->receipt->id,
            $event->traceId,
            $event->eventId,
        );
    }
}
