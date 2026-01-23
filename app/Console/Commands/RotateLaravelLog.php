<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class RotateLaravelLog extends Command
{
    protected $signature = 'logs:rotate';
    protected $description = 'Rotate laravel.log daily at midnight or when size exceeds 3MB';

    public function handle(): int
    {
        $tz = 'Asia/Jakarta';

        $logFile = storage_path('logs/laravel.log');
        $archiveBase = storage_path('logs/archived');
        $maxSize = 3 * 1024 * 1024; // 3MB

        $now = Carbon::now($tz);

        $this->info("=== Rotate check @ {$now->toDateTimeString()} ===");

        if (!file_exists($logFile)) {
            $this->info("laravel.log not found");
            return Command::SUCCESS;
        }

        $size = filesize($logFile);
        $lastModified = Carbon::createFromTimestamp(
            filemtime($logFile),
            $tz
        );

        $shouldRotateBySize = $size >= $maxSize;
        $shouldRotateByDate = !$lastModified->isSameDay($now);

        if (!$shouldRotateBySize && !$shouldRotateByDate) {
            $this->info("Skip rotate. Size: {$size} bytes, Last modified: {$lastModified}");
            return Command::SUCCESS;
        }

        $reason = $shouldRotateBySize
            ? 'SIZE_LIMIT'
            : 'DAILY_ROTATION';

        $owner = fileowner($logFile);
        $group = filegroup($logFile);

        $dateDir = $lastModified->format('Y-m-d');
        $timestamp = $now->format('Y-m-d_H-i-s');

        $archiveDir = "{$archiveBase}/{$dateDir}";
        $archivedLog = "{$archiveDir}/laravel_{$timestamp}_{$reason}.log";

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

        $this->info("Rotated ({$reason}) to: {$archivedLog}");
        $this->info("=== Rotate finished ===");

        return Command::SUCCESS;
    }
}
