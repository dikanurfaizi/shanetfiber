<?php

namespace App\Jobs\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Helpers\LogHelper;
use App\Services\Messaging\Handlers\BroadcastMessageHandler;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendBoardcastNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public WhatsAppAction|string $action,
        public array $broadcastData,
        public ?int $deviceId,
        public ?int $eventId,
        public string $traceId
    ) {
        $this->onQueue('broadcast');
    }

    public function handle(
        MessageDispatcher $dispatcher,
        MessageBuilder $builder
    ): void {
        try {
            $actionSlug = $this->action instanceof WhatsAppAction
                ? $this->action->value
                : $this->action;

            // Dispatcher dieksekusi di dalam Job
            $dispatcher->dispatch(
                action: $actionSlug,
                handlerFactory: fn(string $category) => new BroadcastMessageHandler(
                    action: $this->action,
                    builder: $builder,
                    broadcastData: $this->broadcastData,
                    category: $category
                ),
                traceId: $this->traceId,
                eventId: $this->eventId ?? null,
                deviceId: $this->deviceId ?? null
            );

            LogHelper::logInfo(null, [
                'action'      => $actionSlug,
                'customer_id' => $this->broadcastData['customer_id'] ?? null,
                'receiver'    => $this->broadcastData['target_contact'] ?? null,
            ], 200, '[SendBroadcastJob] Broadcast item successfully dispatched via MessageDispatcher', $this->traceId);
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'action'          => is_string($this->action) ? $this->action : $this->action->value,
                'customer_id'     => $this->broadcastData['customer_id'] ?? null,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, '[SendBroadcastJob] Failed to dispatch broadcast item', $this->traceId);

            throw $e;
        }
    }
}
