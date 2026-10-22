<?php

namespace App\Listeners\Radius;

use App\Events\UnpaymentBillingEvent;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Radius\RadiusUnpaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class HandleRadiusUnpaymentUpdateRequested implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'radius';
    public int $tries = 3;

    public function __construct(
        private RadiusUnpaymentService $radiusUnpaymentService
    ) {}

    public function handle(UnpaymentBillingEvent $event): void
    {
        if (!in_array($event->driver, ['radius', 'both'])) {
            return;
        }

        $timer = new ExecutionTimer();
        $billing = $event->billing;
        $traceId = $event->traceId;

        LogHelper::logInfo('HandleRadiusUnpaymentUpdateRequested', [
            'billing_id'  => $billing->id,
            'customer_id' => $billing->customer_id,
        ], 200, 'Starting asynchronous FreeRADIUS unpayment processing.', $traceId, $timer->lap());

        try {
            // Langsung panggil RadiusUnpaymentService yang memiliki metode disconnectRadiusSession
            $result = $this->radiusUnpaymentService->handle($billing, $traceId);

            if (!empty($result['error'])) {
                LogHelper::logWarning('HandleRadiusUnpaymentUpdateRequested', [
                    'billing_id' => $billing->id,
                    'message'    => $result['message'] ?? 'Unknown error',
                ], 400, 'FreeRADIUS unpayment finished with warning/error response.', $traceId, $timer->lap());

                return;
            }

            LogHelper::logInfo('HandleRadiusUnpaymentUpdateRequested', [
                'billing_id' => $billing->id,
                'result'     => $result,
            ], 200, 'Successfully processed FreeRADIUS unpayment asynchronously.', $traceId, $timer->lap());
        } catch (Throwable $e) {
            LogHelper::logError('HandleRadiusUnpaymentUpdateRequested', [
                'billing_id' => $billing->id,
                'exception'  => class_basename($e),
                'message'    => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ], 500, 'Failed to execute asynchronous FreeRADIUS unpayment listener.', $traceId, $timer->lap());

            throw $e;
        }
    }
}
