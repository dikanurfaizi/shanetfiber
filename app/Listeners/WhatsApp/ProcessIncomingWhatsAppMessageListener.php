<?php

namespace App\Listeners\WhatsApp;

use App\Events\SingleNotification\IncomingWhatsAppMessageEvent;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Notification\WhatsApp\Single\ProcessIncomingWhatsAppMessageService;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class ProcessIncomingWhatsAppMessageListener extends BaseWhatsAppListener
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(protected ProcessIncomingWhatsAppMessageService $service)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(IncomingWhatsAppMessageEvent $event): void
    {
        $timer = new ExecutionTimer();
        $traceId = $event->traceId;

        LogHelper::logInfo(
            null,
            ['event_id' => $event->eventId ?? null],
            200,
            '[IncomingWhatsAppMessageListener] Handling incoming WhatsApp message event',
            $traceId,
            $timer->lap()
        );

        try {
            $this->service->process($event->payload, $traceId, $timer);

            LogHelper::logInfo(
                null,
                ['event_id' => $event->eventId ?? null],
                200,
                '[IncomingWhatsAppMessageListener] Successfully processed incoming WhatsApp message',
                $traceId,
                $timer->lap()
            );
        } catch (Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'event_id' => $event->eventId ?? null,
                    'error'    => $e->getMessage(),
                    'file'     => $e->getFile(),
                    'line'     => $e->getLine(),
                ],
                500,
                '[IncomingWhatsAppMessageListener] Failed processing incoming WhatsApp message',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }
}
