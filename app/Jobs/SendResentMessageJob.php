<?php

namespace App\Jobs;

use App\Models\Messaging\MessageEvent;
use App\Services\Event\ResendEventService;
use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendResentMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $eventId,
        private string $traceId
    ) {}

    public function handle(ResendEventService $service): void
    {
        $timer = new ExecutionTimer();

        LogHelper::logInfo(null, [
            'event_id' => $this->eventId,
            'queue'    => $this->queue ?? 'default'
        ], 200, 'Processing SendResentMessageJob started.', $this->traceId, $timer->lap());

        try {
            // Cari data di DB
            $event = MessageEvent::findOrFail($this->eventId);

            // Ambil state terbaru dari DB
            $event->refresh();

            // Eksekusi service utama
            $service->handle($event, $this->traceId);

            // Segarkan data untuk keperluan log output yang akurat
            $event->refresh();

            LogHelper::logInfo(null, [
                'event_id' => $this->eventId,
                'status'   => $event->status
            ], 200, 'SendResentMessageJob processed successfully.', $this->traceId, $timer->lap());
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'event_id'      => $this->eventId,
                'error_message' => $e->getMessage(),
                'exception'     => get_class($e),
                'trace'         => substr($e->getTraceAsString(), 0, 1000)
            ], 500, 'Failed to process SendResentMessageJob.', $this->traceId, $timer->lap());

            // Lempar kembali exception-nya agar Laravel Queue mendeteksi crash ini sebagai tanda gagal
            throw $e;
        }
    }
}
