<?php

namespace App\Services\Mikrotik\Ppp;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Services\Mikrotik\MikrotikHelper;
use App\Services\Routerboard\Ppp\SecretService;

class MikrotikActivatePppSecret
{
    public function __construct(
        private SecretService $secretService,
        private MikrotikHelper $mikrotikHelper
    ) {}

    public function handle(
        Customer $customer,
        Mikrotik $router,
        string $profile,
        string $commentPayment,
        string $traceId
    ): array {
        $timer = new ExecutionTimer();

        // Eager load relasi pppType untuk menghindari N+1 query
        $customer->loadMissing('pppType');
        $serviceName = $customer->pppType?->name;
        $username = $customer->username;

        // Guard Check: Jika action MikroTik di-disable via config
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'router'      => $router->name,
            ], 200, "Mikrotik activation skipped by config (allow_mikrotik_actions = false)", $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess("Skipped Mikrotik API execution (action disabled by config)");
        }

        try {
            $createSecret = $this->secretService->createSecret(
                $router,
                $username,
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
            $customer->update(['secret_id' => $secretId]);

            $this->secretService->enableSecret($router, $username, $traceId);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'secret_id'   => $secretId,
                'server'      => $router->name,
            ], 200, 'PPPoE customer activated successfully', $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess("PPPoE customer activated successfully");
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'customer_id' => $customer->id,
                    'server'      => $router->name,
                ],
                '[MikrotikActivatePppSecret] Failed to activate PPPoE secret customer',
                $this->mikrotikHelper->getStatusCode($e),
                $traceId,
                $timer->lap()
            );

            return $this->mikrotikHelper->responseError("Cannot connect to the server {$router->name}.");
        }
    }
}
