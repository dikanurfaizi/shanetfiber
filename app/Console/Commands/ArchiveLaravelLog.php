<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use DateTime;
use FilesystemIterator;

class CleanupArchivedLogs extends Command
{
    protected $signature = 'logs:cleanup';
    protected $description = 'Delete archived logs older than 30 days';

    public function handle(): int
    {
        $archiveBase = storage_path('logs/archived');
        $retentionDays = 15;

        $this->info("=== Cleanup archived logs started @ " . now()->toDateTimeString() . " ===");

        if (!File::isDirectory($archiveBase)) {
            $this->info("Archive folder not found.");
            return Command::SUCCESS;
        }

        $cutOff = new DateTime("-{$retentionDays} days");
        $objects = new FilesystemIterator($archiveBase, FilesystemIterator::SKIP_DOTS);

        $deleted = 0;

        foreach ($objects as $object) {
            if (!$object->isDir()) continue;

            $folderName = $object->getBasename();
            $folderDate = DateTime::createFromFormat('Y-m-d', $folderName);

            if ($folderDate && $folderDate < $cutOff) {
                $this->deleteDir($object->getPathname());
                $deleted++;
                $this->info("Deleted: {$folderName}");
            }
        }

        $this->info("Total deleted: {$deleted}");
        $this->info("=== Cleanup finished ===");

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
