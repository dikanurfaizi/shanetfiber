<?php

namespace App\Console\Commands;

use App\Helpers\ExecutionTimer;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Jobs\Network\FetchMikrotikInfo;
use App\Helpers\LogHelper;

class FetchMikrotikInfoCommand extends Command
{
    protected $signature = 'network:fetch-mikrotik-info';
    protected $description = 'Fetch Mikrotik interface, DHCP, and PPPoE information from all registered routers.';

    public function handle(): int
    {
        $traceId = Str::uuid()->toString();
        $timer = new ExecutionTimer();
        $this->info("=== Fetch Mikrotik Info Job started (Trace: {$traceId}) ===");

        try {
            FetchMikrotikInfo::dispatch($traceId)->onQueue('mikrotik-heavy');

            LogHelper::logInfo(null, [], 200, 'Fetch Mikrotik Info job dispatched', $traceId, $timer->lap());

            $this->info("✅ Dispatched FetchMikrotikInfo to queue mikrotik-heavy");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Failed to dispatch FetchMikrotikInfo: " . $e->getMessage());
            LogHelper::logError(null, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Fetch Mikrotik Info failed', $traceId, $timer->lap());

            return Command::FAILURE;
        }
    }
}
