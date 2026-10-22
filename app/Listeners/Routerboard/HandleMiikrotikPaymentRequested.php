<?php

namespace App\Listeners\Routerboard;

use App\Events\BillingPaymentEvent;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Mikrotik\MikrotikPayment;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleMiikrotikPaymentRequested implements ShouldQueue
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
        protected MikrotikPayment $mikrotikPayment
    ) {}

    public function handle(BillingPaymentEvent $event): void
    {
        $timer = new ExecutionTimer();

        if (!in_array($event->driver, ['mikrotik', 'both'])) {
            return;
        }

        $billing = $event->billing;
        $traceId = $event->traceId;

        try {
            $result = $this->mikrotikPayment->handle($billing, $traceId);

            if (isset($result['error'])) {
                LogHelper::logError(
                    null,
                    [
                        'billing_id' => $billing->id,
                    ],
                    500,
                    "[SyncPaymentUpdateToMikrotik] " . $result['message'],
                    $traceId,
                    $timer->lap()
                );

                throw new Exception($result['message']);
            }

            LogHelper::logInfo(
                null,
                [
                    'billing_id' => $billing->id,
                ],
                200,
                "[SyncPaymentUpdateToMikrotik] Payment update to Mikrotik successful",
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
                    'billing_id' => $billing->id,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                "[SyncPaymentUpdateToMikrotik] Failed payment update to Mikrotik",
                $traceId,
                $timer->lap()
            );

            // Throw exception agar job di-retry otomatis oleh Queue Worker
            throw $e;
        }
    }
}
