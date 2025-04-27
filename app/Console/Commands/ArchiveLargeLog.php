<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use App\Helpers\LogHelper;

class ArchiveLargeLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:archive-large';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive laravel.log if size exceeds 3MB and delete old archived logs';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $logPath = storage_path('logs/laravel.log');
        $maxSize = 3 * 1024 * 1024; // 3MB

        // Cek jika log sudah besar dan harus dipindah
        if (File::exists($logPath) && File::size($logPath) >= $maxSize) {
            $this->archiveLog($logPath);
        }

        // Hapus folder log yang lebih dari 1 bulan
        $this->deleteOldArchivedLogs();
    }

    // Fungsi untuk archive log
    private function archiveLog($logPath)
    {
        $date = Carbon::now()->format('Y-m-d');
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $archiveFolder = storage_path("logs/archived/{$date}");

        // Membuat folder jika tidak ada
        if (!File::exists($archiveFolder)) {
            File::makeDirectory($archiveFolder, 0755, true);
        }

        // Pindahkan log ke folder archive
        $archivedLog = "{$archiveFolder}/laravel_{$timestamp}.log";
        File::move($logPath, $archivedLog);

        // Buat file log kosong baru dengan akses yang sesuai
        $newLog = fopen($logPath, 'w');  // Buka file untuk ditulis (buat file baru)
        fclose($newLog);  // Tutup file

        // Set permission file supaya bisa diakses oleh user selain root, misalnya user web server
        chmod($logPath, 0664);  // Memberikan permission read/write kepada owner dan group, serta read kepada others

        $this->info("Archived laravel.log to {$archivedLog} and created new empty log file");
        LogHelper::logInfo(null, null, 200, "Archived laravel.log to {$archivedLog} and created new empty log file");
    }

    // Fungsi untuk menghapus log yang lebih dari 1 bulan
    private function deleteOldArchivedLogs()
    {
        $archivedFolder = storage_path('logs/archived');
        $cutoffDate = Carbon::now()->subMonth();

        // Cek semua folder di archived
        $folders = File::directories($archivedFolder);

        foreach ($folders as $folder) {
            $folderDate = Carbon::createFromFormat('Y-m-d', basename($folder));

            // Jika folder lebih dari 1 bulan
            if ($folderDate->lt($cutoffDate)) {
                // Hapus folder beserta isinya
                File::deleteDirectory($folder);
                $this->info("Deleted archived folder: {$folder}");
                LogHelper::logInfo(null, null, 200, "Deleted archived folder: {$folder}");
            }
        }
    }
}
