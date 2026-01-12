<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RotateLaravelLog extends Command
{
    protected $signature = 'logs:rotate';
    protected $description = 'Rotate laravel.log when size exceeds 3MB';

    public function handle(): int
    {
        $logFile = storage_path('logs/laravel.log');
        $archiveBase = storage_path('logs/archived');

        $maxSize = 3 * 1024 * 1024;

        $this->info("=== Rotate check @ " . now('Asia/Jakarta')->toDateTimeString() . " ===");

        if (!file_exists($logFile)) {
            $this->info("laravel.log not found");
            return Command::SUCCESS;
        }

        $size = filesize($logFile);

        if ($size < $maxSize) {
            $this->info("Skip rotate. Size: {$size} bytes");
            return Command::SUCCESS;
        }

        $owner = fileowner($logFile);
        $group = filegroup($logFile);

        $date = now('Asia/Jakarta')->format('Y-m-d');
        $timestamp = now('Asia/Jakarta')->format('Y-m-d_H-i-s');

        $archiveDir = "{$archiveBase}/{$date}";
        $archivedLog = "{$archiveDir}/laravel_{$timestamp}.log";

        if (!File::isDirectory($archiveDir)) {
            File::makeDirectory($archiveDir, 0775, true);
            @chgrp($archiveDir, $group ?: 'www');
            @chmod($archiveDir, 0775);
        }

        if (!rename($logFile, $archivedLog)) {
            $this->error("FAILED rotating laravel.log");
            return Command::FAILURE;
        }

        touch($logFile);
        chmod($logFile, 0664);
        @chown($logFile, $owner ?: 'www');
        @chgrp($logFile, $group ?: 'www');

        $this->info("Rotated to: {$archivedLog}");
        $this->info("=== Rotate finished ===");

        return Command::SUCCESS;
    }
}
