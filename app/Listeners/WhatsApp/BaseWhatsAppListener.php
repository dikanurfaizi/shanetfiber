<?php

namespace App\Listeners\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Helpers\DelayMessage;
use App\Helpers\ExecutionTimer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Carbon\Carbon;

abstract class BaseWhatsAppListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'notification';
    public $tries = 3;

    /**
     * Action enum wajib didefinisikan di child listener
     */
    protected WhatsAppAction $action;

    /**
     * Logika kalkulasi delay generik untuk seluruh Listener WhatsApp
     */
    public function withDelay(object $event): Carbon|int
    {
        $timer = new ExecutionTimer();

        // 🔹 Resolve DelayMessage langsung via Container agar aman dipanggil oleh Event Dispatcher
        $delayService = app(DelayMessage::class);

        return $delayService->calculation(
            $this->action->value,
            $event->traceId ?? null,
            0,
            $this->queue,
            $timer->lap()
        );
    }
}
