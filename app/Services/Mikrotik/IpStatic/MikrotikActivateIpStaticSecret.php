<?php

namespace App\Services\Mikrotik\IpStatic;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Services\Mikrotik\MikrotikHelper;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\QueueService;

class MikrotikActivateIpStaticSecret
{
    public function __construct(
        private SecretService $secretService,
        private QueueService $queueService,
        private IpStaticService $ipStaticService,
        private MikrotikHelper $mikrotikHelper
    ) {}

    public function handle(
        Customer $customer,
        Mikrotik $router,
        string $profileName,
        string $commentPayment,
        string $traceId
    ): array {
        $timer = new ExecutionTimer();

        // Guard Check: Jika action MikroTik di-disable via config
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'router'      => $router->name,
            ], 200, "Mikrotik activation skipped by config (allow_mikrotik_actions = false)", $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess("Skipped Mikrotik API execution (action disabled by config)");
        }

        try {
            $profileOnMikrotik = $this->secretService->getPppProfileByName($router, $profileName, $traceId);
            $profile = $profileOnMikrotik[0] ?? null;
            $valueLimitasi = $profile['rate-limit'] ?? '';

            if (empty($valueLimitasi)) {
                $error = "Please create limitasi PPP Profile {$profileName} on server {$router->name}!";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'profile'     => $profileName,
                    'server'      => $router->name,
                ], 400, $error, $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($error);
            }

            $parts = explode(' ', $valueLimitasi);
            $maxlimit = $parts[0] ?? '0/0';
            $priority = isset($parts[4]) ? "{$parts[4]}/{$parts[4]}" : '8/8';
            $limitat  = $parts[5] ?? '0/0';

            $ipStatic   = $customer->ip_static;
            $macAddress = $customer->mac_address;
            $interface  = $customer->interface;
            $parent     = $profile['parent-queue'] ?? 'none';
            $queueType  = $profile['queue-type'] ?? '';
            $comment    = $customer->username ?? 'Create from Customer Management';

            // 1. Create Simple Queue
            $createLimitasi = $this->queueService->createSimpleQueue(
                $router,
                $customer->username,
                $ipStatic,
                $parent,
                $priority,
                $queueType,
                $limitat,
                $maxlimit,
                $commentPayment,
                $traceId
            );

            if (isset($createLimitasi['after']['message'])) {
                $error = "Cannot create queue limit on server {$router->name}: {$createLimitasi['after']['message']}.";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'server'      => $router->name,
                    'response'    => $createLimitasi,
                ], 400, $error, $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($error);
            }

            $simpleQueueId = $createLimitasi['after']['ret'];

            // 2. Add ARP Entry
            $createArp = $this->ipStaticService->addIpToArp($router, $ipStatic, $macAddress, $interface, $comment, $traceId);

            if (isset($createArp['after']['message'])) {
                // Rollback Queue jika ARP gagal
                $this->ipStaticService->deleteIpFromSimpleQueue($router, $simpleQueueId, $traceId);

                $error = "Cannot create ARP entry for IP {$ipStatic} on server {$router->name}: {$createArp['after']['message']}.";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'ip_static'   => $ipStatic,
                    'server'      => $router->name,
                    'response'    => $createArp,
                ], 400, $error, $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($error);
            }

            $arpId = $createArp['after']['ret'];

            // 3. Update local DB
            $customer->update([
                'simpleque_id' => $simpleQueueId,
                'arp_id'       => $arpId,
            ]);

            LogHelper::logInfo(null, [
                'customer_id'  => $customer->id,
                'simpleque_id' => $simpleQueueId,
                'arp_id'       => $arpId,
                'server'       => $router->name,
            ], 200, 'IP Static customer activated successfully', $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess("IP Static customer activated successfully");
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'customer_id' => $customer->id,
                    'server'      => $router->name,
                ],
                '[MikrotikActivateIpStaticSecret] Failed to activate IP Static customer',
                $this->mikrotikHelper->getStatusCode($e),
                $traceId,
                $timer->lap()
            );

            return $this->mikrotikHelper->responseError("Cannot connect to the server {$router->name}.");
        }
    }
}
