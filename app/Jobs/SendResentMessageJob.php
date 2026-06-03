<?php

namespace App\Jobs;

use App\Models\Messaging\MessageEvent;
use App\Services\Event\ResendEventService;
use App\Helpers\LogHelper; // Pastikan namespace LogHelper & ExecutionTimer di-import jika diperlukan
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

        // 1. Log awal ketika Job mulai diproses oleh worker
        LogHelper::logInfo(null, [
            'event_id' => $this->eventId,
            'queue' => $this->queue ?? 'default'
        ], 200, 'Processing SendResentMessageJob started.', $this->traceId, $timer->lap());

        try {
            // Ambil data dari DB
            $event = MessageEvent::findOrFail($this->eventId);

            // 🔥 TAMBAHKAN INI: Paksa Laravel untuk mengambil ulang attribute terbaru dari DB
            $event->refresh();

            // Eksekusi service utama
            $service->handle($event, $this->traceId);

            // 2. Log sukses setelah service selesai dieksekusi
            LogHelper::logInfo(null, [
                'event_id' => $this->eventId,
                'status' => $event->status
            ], 200, 'SendResentMessageJob processed successfully.', $this->traceId, $timer->lap());
        } catch (Throwable $e) {
            // 3. Log error jika terjadi kegagalan di dalam job (misal: ModelNotFound atau error dari service)
            LogHelper::logError(null, [
                'event_id' => $this->eventId,
                'error_message' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => substr($e->getTraceAsString(), 0, 1000) // Batasi trace log agar tidak terlalu panjang
            ], 500, 'Failed to process SendResentMessageJob.', $this->traceId, $timer->lap());

            // Lempar kembali exception-nya agar Laravel Queue tahu job ini gagal dan bisa di-retry/fail
            throw $e;
        }
    }
}
