<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use DateTime;
use FilesystemIterator;

class ArchiveLaravelLog extends Command
{
    protected $signature = 'logs:archive';
    protected $description = 'Archive laravel.log daily and delete archives older than 30 days';

    public function handle(): int
    {
        date_default_timezone_set('Asia/Jakarta');

        $logFile       = storage_path('logs/laravel.log');
        $archiveBase   = storage_path('logs/archived');
        $retentionDays = 30;

        $this->info("=== Archiving Laravel log @ " . now('Asia/Jakarta')->toDateTimeString() . " ===");

        // --- 1. Arsipkan log hari ini ---
        if (file_exists($logFile) && filesize($logFile) > 0) {
            $date        = date('Y-m-d');
            $timestamp   = date('Y-m-d_H-i-s');
            $archiveDir  = "{$archiveBase}/{$date}";
            $archivedLog = "{$archiveDir}/laravel_{$timestamp}.log";

            if (!File::isDirectory($archiveDir)) {
                File::makeDirectory($archiveDir, 0755, true);
            }

            if (rename($logFile, $archivedLog)) {
                $this->info("✅ Log archived to {$archivedLog}");
                touch($logFile);
                chmod($logFile, 0644);
            } else {
                $this->error("❌ Failed to move log to {$archivedLog}");
                return Command::FAILURE;
            }
        } else {
            $this->line("ℹ️ Log empty or not found, skipping archive.");
        }

        // --- 2. Hapus folder arsip lama (>30 hari) ---
        $cutOff = new DateTime("-{$retentionDays} days");
        if (!File::isDirectory($archiveBase)) {
            $this->warn("No archived logs found to clean.");
        } else {
            $objects = new FilesystemIterator($archiveBase, FilesystemIterator::SKIP_DOTS);
            foreach ($objects as $object) {
                if (!$object->isDir()) continue;

                $folderName = $object->getBasename();
                $folderDate = DateTime::createFromFormat('Y-m-d', $folderName);
                if ($folderDate === false) continue;

                if ($folderDate < $cutOff) {
                    $this->deleteDir($object->getPathname());
                    $this->warn("🗑️ Deleted archive {$folderName} (older than {$retentionDays} days)");
                }
            }
        }

        $this->info("✅ Archive process completed successfully.");
        return Command::SUCCESS;
    }

    private function deleteDir(string $dir): void
    {
        $items = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
        foreach ($items as $item) {
            $item->isDir()
                ? $this->deleteDir($item->getPathname())
                : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}
