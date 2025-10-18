<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Jobs\Network\ScanOltsJob;
use App\Helpers\LogHelper;

class ScanOltsCommand extends Command
{
    protected $signature = 'network:scan-olts';
    protected $description = 'Scan all registered OLTs for ONT status and optical power metrics.';

    public function handle(): int
    {
        $traceId = Str::uuid()->toString();
        $username = 'scheduler system';

        $this->info("=== OLT Scan Job started (Trace: {$traceId}) ===");

        try {
            ScanOltsJob::dispatch($traceId, $username)->onQueue('mikrotik-heavy');

            LogHelper::logInfo('ScanOltsCommand', [
                'trace_id' => $traceId,
                'user' => $username,
            ], 200, 'OLT scan job dispatched', $traceId);

            $this->info("✅ Dispatched ScanOltsJob to queue mikrotik-heavy");
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("❌ Failed to dispatch ScanOltsJob: " . $e->getMessage());
            LogHelper::logError('ScanOltsCommand', [
                'trace_id' => $traceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'OLT Scan failed', $traceId);

            return Command::FAILURE;
        }
    }
}
