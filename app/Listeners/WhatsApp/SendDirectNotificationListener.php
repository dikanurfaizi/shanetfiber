<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Events\SingleNotification\DirectMessageRequested;
use App\Helpers\LogHelper;
use App\Services\Notification\WhatsApp\Single\DirectMessageNotificationService;
use Throwable;

class SendDirectNotificationListener extends BaseWhatsAppListener
{
    protected WhatsAppAction $action = WhatsAppAction::DIRECT_MESSAGE;

    /**
     * Inject Service via Constructor
     */
    public function __construct(
        private DirectMessageNotificationService $directMessageService
    ) {}

    /**
     * Listener handle hanya menerima 1 argumen ($event)
     */
    public function handle(DirectMessageRequested $event): void
    {
        try {
            $this->directMessageService->send(
                eventId: $event->eventId,
                traceId: $event->traceId
            );
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;
            LogHelper::logError(
                null,
                [
                    'action'          => $this->action,
                    'event_id'        => $event->eventId,
                    'trace_id'        => $event->traceId,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[SendDirectNotificationListener] Failed to handle DirectMessageRequested event'
            );

            throw $e;
        }
    }
}
