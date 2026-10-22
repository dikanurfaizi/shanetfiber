<?php

namespace App\Listeners\Routerboard;

use App\Events\UserRegisteredEvent;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Mikrotik\MikrotikActivation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Exception;

class SyncCustomerToMikrotik implements ShouldQueue
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
     * Retry 3 kali jika terjadi transient error pada DB RADIUS
     */
    public $tries = 3;

    public function __construct(
        protected MikrotikActivation $mikrotikActivation
    ) {}

    public function handle(UserRegisteredEvent $event): void
    {
        $timer = new ExecutionTimer();

        if (!in_array($event->driver, ['mikrotik', 'both'])) {
            return;
        }

        $customer = $event->customer;
        $traceId = $event->traceId;

        try {
            $result = $this->mikrotikActivation->handle(
                $customer,
                $customer->activeSubscription,
                $traceId
            );

            if (isset($result['error'])) {
                LogHelper::logError(
                    null,
                    [
                        'customer_id' => $customer->id,
                    ],
                    500,
                    "[SyncCustomerToMikrotik] " . $result['message'],
                    $traceId,
                    $timer->lap()
                );

                throw new Exception($result['message']);
            }

            LogHelper::logInfo(
                null,
                [
                    'customerId' => $customer->id
                ],
                200,
                "[SyncCustomerToMikrotik] Success provisioning Mikrotik user: {$customer->username}",
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
                    'customerId' => $customer->id,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                "[SyncCustomerToMikrotik] Failed provisioning Mikrotik user",
                $traceId,
                $timer->lap()
            );

            // Throw exception agar job di-retry otomatis oleh Queue Worker
            throw $e;
        }
    }
}
