<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Events\SingleNotification\PackageChangesRequest;
use App\Jobs\Notification\SendPackageChangesNotificationJob;
use App\Services\Notification\WhatsApp\Single\PackageChangesNotificationService;

class SendPackageChangesListener extends BaseWhatsAppListener
{
    protected WhatsAppAction $action = WhatsAppAction::CHANGES_PACKAGE;

    /**
     * Inject Service via Constructor
     */
    public function __construct(
        protected PackageChangesNotificationService $service
    ) {}

    /**
     * Listener handle hanya menerima 1 argumen ($event)
     */
    public function handle(PackageChangesRequest $event): void
    {
        $this->service->send(
            action: $this->action,
            customer: $event->customer,
            oldPackageName: $event->oldPackageName,
            oldPackagePrice: $event->oldPackagePrice,
            eventId: $event->eventId ?? null,
            traceId: $event->traceId
        );
    }
}
