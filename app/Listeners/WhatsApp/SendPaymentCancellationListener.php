<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Events\SingleNotification\PaymentCancelled;
use App\Events\UnpaymentBillingEvent;
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
    public function handle(UnpaymentBillingEvent $event): void
    {
        $this->service->send(
            action: $this->action,
            receiptId: $event->receiptId,
            billingId: $event->billing->id,
            receiptData: $event->receiptData,
            eventId: $event->eventId,
            traceId: $event->traceId
        );
    }
}
