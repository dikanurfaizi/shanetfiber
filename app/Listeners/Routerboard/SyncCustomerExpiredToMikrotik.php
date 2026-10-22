<?php

namespace App\Listeners\Routerboard;

use App\Events\UserVoidEvent;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Mikrotik\MikrotikExpired;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Exception;

class SyncCustomerExpiredToMikrotik implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Menggunakan queue driver bawaan Laravel (misal: 'database' atau 'redis')
     */
    public $connection = 'database';

    /**
     * Nama antrean khusus (queue tube/channel) untuk Supervisor Worker Anda
     */
    public $queue = 'mikrotik';

    /**
     * Retry 3 kali jika terjadi transient error
     */
    public $tries = 3;

    public function __construct(
        protected MikrotikExpired $mikrotikExpired
    ) {}

    public function handle(UserVoidEvent $event): void
    {
        $timer = new ExecutionTimer();

        if (!in_array($event->driver, ['mikrotik', 'both'])) {
            return;
        }

        $customer = $event->customer;
        $package = $event->package;
        $traceId = $event->traceId;

        // Ensure eager loading untuk relasi mikrotik
        $customer->loadMissing(['mikrotik']);

        try {
            $result = $this->mikrotikExpired->handle($customer, $package, $traceId);

            if (isset($result['error'])) {
                throw new Exception($result['message']);
            }

            LogHelper::logInfo(
                null,
                [
                    'customerId' => $customer->id,
                    'packageId'  => $package?->id,
                    'profile'    => $package?->profile,
                ],
                200,
                "[MikrotikSync] Success set expired Mikrotik user: {$customer->name}",
                $traceId,
                $timer->lap()
            );
        } catch (Exception $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(
                null,
                [
                    'customerId'      => $customer->id,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                "[MikrotikSync] Failed set expired Mikrotik user",
                $traceId,
                $timer->lap()
            );

            // Throw exception agar job di-retry otomatis oleh Queue Worker
            throw $e;
        }
    }
}
