<?php

namespace App\Listeners\Mikrotik;

use App\Events\Routerboard\MikrotikUnpaymentUpdateRequested;
use App\Events\UnpaymentBillingEvent;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Mikrotik\MikrotikUnpayment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class HandleMikrotikUnpaymentRequested implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Nama queue khusus untuk proses heavy Mikrotik API.
     */
    public string $queue = 'mikrotik-heavy';

    /**
     * Jumlah percobaan jika job mengalami kegagalan.
     */
    public int $tries = 3;

    public function __construct(
        private MikrotikUnpayment $mikrotikUnpayment
    ) {}

    /**
     * Jalankan listener secara asynchronous.
     */
    public function handle(UnpaymentBillingEvent $event): void
    {
        if (!in_array($event->driver, ['mikrotik', 'both'])) {
            return;
        }

        $timer = new ExecutionTimer();
        $billing = $event->billing;
        $traceId = $event->traceId;

        LogHelper::logInfo('HandleMikrotikUnpaymentRequested', [
            'billing_id'  => $billing->id,
            'customer_id' => $billing->customer_id,
        ], 200, 'Starting asynchronous Mikrotik unpayment processing.', $traceId, $timer->lap());

        try {
            $result = $this->mikrotikUnpayment->handle($billing, $traceId);

            if (!empty($result['error'])) {
                LogHelper::logWarning('HandleMikrotikUnpaymentRequested', [
                    'billing_id' => $billing->id,
                    'message'    => $result['message'] ?? 'Unknown error',
                ], 400, 'Mikrotik unpayment finished with warning/error response.', $traceId, $timer->lap());

                return;
            }

            LogHelper::logInfo('HandleMikrotikUnpaymentRequested', [
                'billing_id' => $billing->id,
                'result'     => $result,
            ], 200, 'Successfully processed Mikrotik unpayment asynchronously.', $traceId, $timer->lap());
        } catch (Throwable $e) {
            LogHelper::logError('HandleMikrotikUnpaymentRequested', [
                'billing_id' => $billing->id,
                'exception'  => class_basename($e),
                'message'    => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ], 500, 'Failed to execute asynchronous Mikrotik unpayment listener.', $traceId, $timer->lap());

            throw $e; // Re-throw agar job dicatat sebagai failed di Queue
        }
    }
}
