<?php

namespace App\Listeners\Radius;

use App\Events\Radius\RadiusProfileUpdateRequested;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Radius\RadiusProfileService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class HandleRadiusGroupUpdateRequested implements ShouldQueue
{
    use InteractsWithQueue;

    public string $connection = 'database';
    public string $queue = 'radius';
    public int $tries = 3;

    public function __construct(
        private RadiusProfileService $radiusProfileService
    ) {}

    public function handle(RadiusProfileUpdateRequested $event): void
    {
        $timer = new ExecutionTimer();
        $traceId = $event->traceId;

        LogHelper::logInfo(null, [], 200, 'Starting asynchronous PPP profiles synchronization to FreeRADIUS.', $traceId, $timer->lap());

        try {
            $syncedCount = $this->radiusProfileService->syncAllProfiles($traceId);

            if ($syncedCount === 0) {
                LogHelper::logWarning(null, [], 200, 'No active PPP profiles found to sync with FreeRADIUS.', $traceId, $timer->lap());
                return;
            }

            LogHelper::logInfo(null, [
                'synced_count' => $syncedCount,
            ], 200, "Successfully synced {$syncedCount} PPP Profile(s) to FreeRADIUS.", $traceId, $timer->lap());
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'exception_class' => get_class($e),
                'error_message'   => $e->getMessage(),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], 500, 'Failed syncing PPP profiles to FreeRADIUS.', $traceId, $timer->lap());

            // Throw exception agar job di-retry otomatis oleh Queue Worker
            throw $e;
        }
    }
}
