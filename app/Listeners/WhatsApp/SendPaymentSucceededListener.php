<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Events\SingleNotification\PaymentSucceeded;
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
    public function handle(PaymentSucceeded $event): void
    {
        $this->service->send(
            $this->action,
            $event->receiptId,
            $event->traceId,
            $event->eventId,
        );
    }
}
