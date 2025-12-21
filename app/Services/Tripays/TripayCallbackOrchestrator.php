<?php

namespace App\Services\Tripays;

use App\Enums\OrderStatus;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Billings\Order;
use Carbon\Carbon;
use App\Services\Billings\PaymentCancellationService;

class TripayCallbackOrchestrator
{
    public function __construct(
        private PaidPaymentService $paidPaymentService,
        private PaymentCancellationService $paymentCancellationService,
        private UnpaidPaymentService $unpaidPaymentService,
        private CancelExpiredPaymentService $cancelExpiredPaymentService,
    ) {}

    /**
     * Handle PAID callback
     */
    public function handlePaid(
        Order $order,
        Carbon $paidAt,
        string $traceId
    ): void {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(null, [
            'order_id' => $order->id,
            'status'   => 'PAID',
        ], 200, '[Tripay][Orchestrator] handlePaid start', $traceId, $timer->lap());

        $billing = $order->billing;

        if (!$billing) {
            throw new \DomainException(
                "Billing not found for order {$order->id}"
            );
        }

        $this->paidPaymentService->handle(
            order: $order,
            billing: $billing,
            paymentTime: $paidAt,
            traceId: $traceId,
        );

        LogHelper::logInfo(null, [
            'order_id' => $order->id,
        ], 200, '[Tripay][Orchestrator] handlePaid completed', $traceId, $timer->lap());
    }

    public function handleUnpaid(Order $order, string $traceId): void
    {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(null, [
            'order_id' => $order->id,
            'status'   => 'PAID',
        ], 200, '[Tripay][Orchestrator] handleUnpaid start', $traceId, $timer->lap());

        $billing = $order->billing;

        if (!$billing) {
            throw new \DomainException(
                "Billing not found for order {$order->id}"
            );
        }

        $this->unpaidPaymentService->handle(
            order: $order,
            billing: $billing,
            traceId: $traceId,
        );

        if (config('services.shanetfiber.allow_mikrotik_actions')) {
            $this->paymentCancellationService->processMikrotikCancellation($order->customer, $traceId);
        }

        LogHelper::logInfo(null, [
            'order_id' => $order->id,
        ], 200, '[Tripay][Orchestrator] handleUnpaid completed', $traceId, $timer->lap());
    }

    public function handleExpired(Order $order, string $status, string $traceId): void
    {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(null, [
            'order_id' => $order->id,
            'status'   => 'PAID',
        ], 200, '[Tripay][Orchestrator] handleExpired start', $traceId, $timer->lap());

        $this->cancelExpiredPaymentService->handle(
            order: $order,
            status: $status,
            traceId: $traceId
        );

        LogHelper::logInfo(null, [
            'order_id' => $order->id,
        ], 200, '[Tripay][Orchestrator] handleExpired completed', $traceId, $timer->lap());
    }

    public function handleCanceled(Order $order, string $status, string $traceId): void
    {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(null, [
            'order_id' => $order->id,
            'status'   => 'PAID',
        ], 200, '[Tripay][Orchestrator] handleCanceled start', $traceId, $timer->lap());

        $this->cancelExpiredPaymentService->handle(
            order: $order,
            status: $status,
            traceId: $traceId
        );

        LogHelper::logInfo(null, [
            'order_id' => $order->id,
        ], 200, '[Tripay][Orchestrator] handleCanceled completed', $traceId, $timer->lap());
    }
}
