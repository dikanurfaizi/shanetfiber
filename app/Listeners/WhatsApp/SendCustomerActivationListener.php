<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Events\SingleNotification\ActivateCustomerEvent;
use App\Events\UserRegisteredEvent;
use App\Helpers\LogHelper; // Pastikan LogHelper di-import
use App\Services\Notification\WhatsApp\Single\CustomerActivationNotificationService;
use Throwable;

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
        $eventClass = class_basename($event);

        LogHelper::logInfo(
            null,
            [
                'event_type'             => $eventClass,
                'customer_membership_id' => $event->customerMembershipId ?? null,
                'event_id'               => $event->eventId ?? null,
                'billing_id'            => $event->billing?->id ?? null,
                'receipt_id'            => $event->receipt?->id ?? null,
            ],
            200,
            "[ACTIVATION_LISTENER] Handling {$eventClass}",
            $event->traceId
        );

        try {
            $this->service->send(
                action: $this->action,
                customerMembershipId: $event->customerMembershipId,
                customerSubscription: $event->customerSubscription,
                billing: $event->billing,
                receipt: $event->receipt,
                eventId: $event->eventId,
                traceId: $event->traceId
            );

            LogHelper::logInfo(
                null,
                [
                    'event_type' => $eventClass,
                    'event_id'   => $event->eventId ?? null,
                ],
                200,
                "[ACTIVATION_LISTENER] Successfully processed {$eventClass}",
                $event->traceId
            );
        } catch (Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'event_type' => $eventClass,
                    'event_id'   => $event->eventId ?? null,
                    'error'      => $e->getMessage(),
                    'file'       => $e->getFile(),
                    'line'       => $e->getLine(),
                ],
                500,
                "[ACTIVATION_LISTENER] Failed processing {$eventClass}",
                $event->traceId
            );

            throw $e; // Lempar kembali exception agar queue retry/failed job berjalan
        }
    }
}
