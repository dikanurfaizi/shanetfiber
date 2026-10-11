<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class RotateLaravelLog extends Command
{
    protected $signature = 'logs:rotate';
    protected $description = 'Rotate laravel.log daily at 23:59 or when size exceeds 3MB';

    public function handle(): int
    {
        $tz = 'Asia/Jakarta';

        $logFile = storage_path('logs/laravel.log');
        $archiveBase = storage_path('logs/archived');
        $maxSize = 2 * 1024 * 1024; // 2MB

        $now = Carbon::now($tz);

        $this->info("=== Rotate check @ {$now->toDateTimeString()} ===");

        if (!file_exists($logFile)) {
            $this->info("laravel.log not found");
            return Command::SUCCESS;
        }

        $size = filesize($logFile);

        // Cek 1: Apakah ukuran file mendekati/melebihi 3MB?
        $shouldRotateBySize = $size >= $maxSize;

        // Cek 2: Apakah ini rotasi utama akhir hari? (Pukul 23:59)
        $shouldRotateDaily = ($now->format('H:i') === '23:59');

        // Jika dua kondisi di atas tidak terpenuhi, JANGAN di-archive dulu
        if (!$shouldRotateBySize && !$shouldRotateDaily) {
            $this->info("Skip rotate. Size: " . round($size / 1024 / 1024, 2) . " MB. Belum 23:59 atau belum 3MB.");
            return Command::SUCCESS;
        }

        // Tentukan prefix nama file sesuai instruksi Anda
        $prefix = $shouldRotateBySize ? 'reach_max_limit_' : '';

        $owner = fileowner($logFile);
        $group = filegroup($logFile);

        // Folder arsip menggunakan tanggal hari ini (misal: 2026-06-10)
        $dateDir = $now->format('Y-m-d');
        $timestamp = $now->format('H-i-s');

        $archiveDir = "{$archiveBase}/{$dateDir}";

        // Hasil akhir nama file contoh: reach_max_limit_laravel_14-30-15.log ATAU laravel_23-59-00.log
        $archivedLog = "{$archiveDir}/{$prefix}laravel_{$timestamp}.log";

        if (!File::isDirectory($archiveDir)) {
            File::makeDirectory($archiveDir, 0775, true);
            @chgrp($archiveDir, $group ?: 'www');
            @chmod($archiveDir, 0775);
        }

        // Proses pemindahan file log lama ke folder archive
        if (!rename($logFile, $archivedLog)) {
            $this->error("FAILED rotating laravel.log");
            return Command::FAILURE;
        }

        // Buat ulang file laravel.log yang kosong dengan permission yang benar
        touch($logFile);
        chmod($logFile, 0775);
        @chown($logFile, $owner ?: 'www');
        @chgrp($logFile, $group ?: 'www');

        $this->info("Rotated successfully to: {$archivedLog}");
        $this->info("=== Rotate finished ===");

        return Command::SUCCESS;
    }
}
