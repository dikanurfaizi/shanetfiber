<?php

namespace App\Services\Tripays;

use App\Helpers\LogHelper;
use App\Models\Billings\Order;

class CancelExpiredPaymentService
{
    public function handle(Order $order, string $status, string $traceId): void
    {
        $oldStatus = $order->payment_status;

        $order->update(['payment_status' => $status]);

        LogHelper::logInfo(null, [
            'order_id'    => $order->id,
            'old_status'  => $oldStatus,
            'new_status'  => $status,
        ], 200, "[Tripay] Update Payment Status", $traceId);
    }
}
