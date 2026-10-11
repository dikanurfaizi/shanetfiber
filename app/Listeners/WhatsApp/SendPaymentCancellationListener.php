<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Events\SingleNotification\PaymentCancelled;
use App\Services\Notification\WhatsApp\Single\PaymentCancellationNotificationService;

class SendPaymentCancellationListener extends BaseWhatsAppListener
{
    protected WhatsAppAction $action = WhatsAppAction::UNPAYMENT;

    /**
     * Inject Service via Constructor
     */
    public function __construct(
        protected PaymentCancellationNotificationService $service
    ) {}

    /**
     * Listener handle hanya menerima 1 argumen ($event)
     */
    public function handle(PaymentCancelled $event): void
    {
        $this->service->send(
            action: $this->action,
            receiptId: $event->receiptId,
            billingId: $event->billingId,
            receiptData: $event->receiptData,
            eventId: $event->eventId ?? null,
            traceId: $event->traceId
        );
    }
}
