<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DeleteOldMessageLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:delete-old-messages';

    /**
     * The console command description.
     */
    protected $description = 'Delete message_events records older than 14 days (keep only last 14 days)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // subDays(15) artinya menyimpan data 15 hari terakhir. 
        // Data hari ke-16 dan seterusnya ke belakang akan dihapus.
        $thresholdDate = Carbon::now()->subDays(15);

        $this->info("⏳ Memulai retensi data. Menghapus data sebelum: {$thresholdDate->toDateTimeString()}...");

        $this->message_events_delete($thresholdDate);
    }

    private function message_events_delete($thresholdDate)
    {
        // Karena data Anda sedikit (hanya kisaran ribuan), direct delete di bawah ini sangat aman dan cepat.
        $deleted = DB::table('message_events')
            ->where('created_at', '<', $thresholdDate)
            ->delete();

        $this->info("✅ Sukses! Berhasil menghapus $deleted baris log pesan lama.");
    }
}
