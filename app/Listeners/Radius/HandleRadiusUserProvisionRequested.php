<?php

namespace App\Listeners\Radius;

use App\Events\Radius\RadiusUserProvisionRequested;
use App\Events\UserRegisteredEvent;
use App\Events\UserVoidEvent;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Radius\RadiusProvisioningStateService;
use App\Services\Radius\RadiusUserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Exception;

class HandleRadiusUserProvisionRequested implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Menggunakan queue driver bawaan Laravel (misal: 'database' atau 'redis')
     */
    public $connection = 'database';

    /**
     * Nama antrean khusus (queue tube/channel) untuk Supervisor Worker Anda
     */
    public $queue = 'radius';

    /**
     * Retry 3 kali jika terjadi transient error pada DB RADIUS
     */
    public $tries = 3;

    public function __construct(
        protected RadiusUserService $radiusUserService,
        protected RadiusProvisioningStateService $radiusProvisioningStateService
    ) {}

    public function handle(UserRegisteredEvent|RadiusUserProvisionRequested|UserVoidEvent $event): void
    {
        $timer = new ExecutionTimer();

        if ($event instanceof UserRegisteredEvent || $event instanceof UserVoidEvent) {
            if (!in_array($event->driver, ['radius', 'both'])) {
                return;
            }
        }

        $customer = $event->customer;
        $pppProfile = $event->pppProfile;
        $traceId = $event->traceId;

        LogHelper::logInfo('HandleRadiusUserProvisionRequested', [
            'customer_id' => $customer->id,
            'username'    => $customer->username,
            'profile'     => $pppProfile?->profile_name,
        ], 200, 'Starting asynchronous FreeRADIUS user provisioning.', $traceId, $timer->lap());

        try {
            // Ambil kredensial PPPoE dari Customer (sesuaikan nama atribut kolomnya)
            $username = $customer->username ?? $customer->contact;
            $password = $customer->password_ppp ?? '123456';
            $framedIp = $customer->ip_address ?? null;

            // 1. Eksekusi Sinkronisasi Data Pelanggan & Paket Bandwidth
            $success = $this->radiusUserService->createOrUpdateUser(
                username: $username,
                password: $password,
                profile: $event->pppProfile,
                framedIp: $framedIp
            );

            // 2. Update Status User
            if (!$customer->is_active) {
                $this->radiusUserService->isolateUser($username);
            } else {
                $this->radiusUserService->unisolateUser($username);
            }

            // 3. Update status keberhasilan di tabel customer_provisioning_states
            $this->radiusProvisioningStateService->updateProvisioningState(
                customerId: $customer->id,
                target: 'radius',
                status: 'synced'
            );

            LogHelper::logInfo('HandleRadiusUserProvisionRequested', [
                'customer_id' => $customer->id,
                'username'    => $customer->username,
                'success'     => $success,
            ], 200, '[HandleRadiusUserProvisionRequested] Successfully provisioned user on FreeRADIUS.', $traceId, $timer->lap());
        } catch (Exception $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(
                null,
                [
                    'customer_id' => $customer->id,
                    'username'    => $customer->username,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                "[HandleRadiusUserProvisionRequested] Failed provisioning RADIUS user",
                $traceId,
                $timer->lap()
            );

            // Throw exception agar job di-retry otomatis oleh Queue Worker
            throw $e;
        }
    }
}
