<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;

class DeleteOldMessageLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:delete-old-messages';

    /**
     * The console command description.
     */
    protected $description = 'Delete message_events records created before the 1st of the current month';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $traceId = (string) Str::uuid();
        $timer   = new ExecutionTimer();

        // Mengambil awal bulan berjalan pukul 00:00:00 (misal: 1 September 2026 00:00:00)
        $thresholdDate = Carbon::now()->startOfMonth();

        $this->info("⏳ Memulai retensi data bulanan.");
        $this->line("• Threshold : {$thresholdDate->toDateTimeString()}");
        $this->line("• Trace ID  : {$traceId}");

        LogHelper::logInfo(
            null,
            [
                'threshold_date' => $thresholdDate->toDateTimeString(),
            ],
            200,
            'Starting monthly message_events cleanup process',
            $traceId,
            $timer->lap()
        );

        try {
            // Menghapus record yang dibuat sebelum tanggal 1 bulan ini pukul 00:00:00
            $deleted = DB::table('message_events')
                ->where('created_at', '<', $thresholdDate)
                ->delete();

            $this->info("✅ Sukses! Berhasil menghapus {$deleted} baris log pesan lama.");

            LogHelper::logInfo(
                null,
                [
                    'deleted_rows'   => $deleted,
                    'threshold_date' => $thresholdDate->toDateTimeString(),
                ],
                200,
                'Monthly message_events cleanup completed successfully',
                $traceId,
                $timer->lap()
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Cleanup message logs process failed.');

            LogHelper::logError(
                null,
                [
                    'threshold_date' => $thresholdDate->toDateTimeString(),
                    'error_message'  => $e->getMessage(),
                    'file'           => $e->getFile(),
                    'line'           => $e->getLine(),
                ],
                500,
                'Failed to cleanup old message_events logs',
                $traceId,
                $timer->lap()
            );

            return Command::FAILURE;
        }
    }
}
