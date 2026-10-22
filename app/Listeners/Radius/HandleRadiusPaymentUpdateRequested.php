<?php

namespace App\Listeners\Radius;

use App\Events\BillingPaymentEvent;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Radius\RadiusUserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class HandleRadiusPaymentUpdateRequested implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Queue khusus untuk transaksi FreeRADIUS.
     */
    public string $queue = 'radius';

    /**
     * Jumlah percobaan ulang jika terjadi kegagalan.
     */
    public int $tries = 3;

    public function __construct(
        private RadiusUserService $radiusUserService
    ) {}

    public function handle(BillingPaymentEvent $event): void
    {
        $timer = new ExecutionTimer();

        if (!in_array($event->driver, ['radius', 'both'])) {
            return;
        }

        $billing = $event->billing;
        $traceId = $event->traceId;

        $billing->loadMissing(['customer.activeSubscription.package.pppProfile']);
        $customer = $billing->customer;
        $profile = $customer?->activeSubscription?->package?->pppProfile;

        LogHelper::logInfo('HandleRadiusPaymentUpdateRequested', [
            'billing_id'  => $billing->id,
            'customer_id' => $customer?->id,
            'username'    => $customer?->username,
        ], 200, 'Starting asynchronous FreeRADIUS payment update.', $traceId, $timer->lap());

        try {
            if (!$customer) {
                LogHelper::logWarning('HandleRadiusPaymentUpdateRequested', [
                    'billing_id' => $billing->id,
                ], 404, 'Customer not found for billing payment update.', $traceId, $timer->lap());
                return;
            }

            // Buka isolir & kembalikan groupname ke profil paket langganan aktif
            $success = $this->radiusUserService->unisolateUser($customer->username, $profile);

            LogHelper::logInfo('HandleRadiusPaymentUpdateRequested', [
                'billing_id'  => $billing->id,
                'customer_id' => $customer->id,
                'username'    => $customer->username,
                'profile'     => $profile?->profile_name,
                'success'     => $success,
            ], 200, 'Successfully updated payment status on FreeRADIUS.', $traceId, $timer->lap());
        } catch (Throwable $e) {
            LogHelper::logError('HandleRadiusPaymentUpdateRequested', [
                'billing_id'  => $billing->id,
                'customer_id' => $customer?->id,
                'exception'   => class_basename($e),
                'message'     => $e->getMessage(),
            ], 500, 'Failed to update payment status on FreeRADIUS.', $traceId, $timer->lap());

            throw $e;
        }
    }
}
