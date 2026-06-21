<?php

namespace App\Jobs\Whatsapp;

use App\Helpers\ExecutionTimer; // 👈 Tetap import di sini
use App\Helpers\LogHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMessageAckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Constructor hanya menerima data yang aman diserialisasi oleh Queue Manager
     */
    public function __construct(
        public array $payload,
        public string $traceId
    ) {
        $this->onQueue('notification');
    }

    public function handle(): void
    {
        // 🌟 BARU & AMAN: Bikin timer baru khusus untuk menghitung durasi kerja worker internal job ini
        $jobTimer = new ExecutionTimer();

        // TODO: update status pesan di DB (READ / DELIVERED / SENT), dsb


        // Di akhir baris, gunakan $jobTimer->lap() untuk mencatat lama eksekusi update DB Anda
        LogHelper::logInfo(null, [
            'receipt_type' => $this->payload['payload']['receipt_type'] ?? '-',
            'chat_id'      => $this->payload['payload']['chat_id'] ?? '-',
        ], 200, '[Job] ProcessMessageAckJob finished successfully.', $this->traceId, $jobTimer->lap());
    }
}
