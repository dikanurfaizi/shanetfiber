<?php

namespace App\Services\Mikrotik\Ppp;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Services\Mikrotik\MikrotikHelper;
use App\Services\Routerboard\Ppp\SecretService;

class MikrotikExportPppSecret
{
    public function __construct(
        private SecretService $secretService,
        private MikrotikHelper $mikrotikHelper
    ) {}

    public function handle(Customer $customer, Mikrotik $router, string $profile, string $commentPayment, string $traceId): array
    {
        $timer = new ExecutionTimer();
        $serviceName = $customer->pppType?->name;

        // 1. Pengecekan Config Paling Akhir Sebelum Hit API
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            // Tetap jalankan update database lokal jika diperlukan
            $customer->update([
                'activation' => true,
                'is_active'  => true,
            ]);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'server'      => $router->name,
            ], 200, 'PPPoE secret export skipped (allow_mikrotik_actions = false)', $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess('Skipped Mikrotik API execution (action disabled by config)');
        }

        // 2. Jika config = true, jalankan transaksi ke Router fisik
        try {
            $createSecret = $this->secretService->createSecret(
                $router,
                $customer->username,
                $customer->password_ppp,
                $serviceName,
                $profile,
                $commentPayment,
                $traceId
            );

            if (isset($createSecret['after']['message'])) {
                $error = "Cannot create PPP user on server {$router->name}.";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'server'      => $router->name,
                    'response'    => $createSecret,
                ], 400, $error, $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($error);
            }

            $secretId = $createSecret['after']['ret'];
            $customer->update([
                'activation' => true,
                'is_active'  => true,
                'secret_id'  => $secretId,
            ]);

            $this->secretService->enableSecret($router, $customer->username, $traceId);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'secret_id'   => $secretId,
                'server'      => $router->name,
            ], 200, 'PPPoE secret exported', $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess('Successfully exported PPPoE secret for customer');
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'customer_id' => $customer->id,
                    'server'      => $router->name,
                ],
                '[MikrotikExportPppSecret] Failed to export PPPoE secret',
                $this->mikrotikHelper->getStatusCode($e),
                $traceId,
                $timer->lap()
            );

            return $this->mikrotikHelper->responseError("Cannot connect to the server {$router->name}.");
        }
    }
}
