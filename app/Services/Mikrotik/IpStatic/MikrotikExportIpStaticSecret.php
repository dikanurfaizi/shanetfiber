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

class MikrotikExportIpStaticSecret
{
    public function __construct(
        private SecretService $secretService,
        private QueueService $queueService,
        private IpStaticService $ipStaticService,
        private MikrotikHelper $mikrotikHelper
    ) {}

    public function handle(Customer $customer, Mikrotik $router, string $profileName, string $commentPayment, string $traceId): array
    {
        $timer = new ExecutionTimer();

        // Guard: Pengecekan Config Paling Awal Sebelum Hit API Mikrotik
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            $customer->update([
                'activation' => true,
                'is_active'  => true,
            ]);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'server'      => $router->name,
            ], 200, 'IP Static export skipped (allow_mikrotik_actions = false)', $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess('Skipped Mikrotik API execution (action disabled by config)');
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

            $createLimitasi = $this->queueService->createSimpleQueue(
                $router,
                $customer->username,
                $customer->ip_static,
                $profile['parent-queue'] ?? 'none',
                $priority,
                $profile['queue-type'] ?? '',
                $limitat,
                $maxlimit,
                $commentPayment,
                $traceId
            );

            if (isset($createLimitasi['after']['message'])) {
                $error = "Cannot create queue limit on server {$router->name}, {$createLimitasi['after']['message']}.";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'server'      => $router->name,
                    'response'    => $createLimitasi,
                ], 400, $error, $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($error);
            }

            $simpleQueueId = $createLimitasi['after']['ret'];
            $createArp = $this->ipStaticService->addIpToArp(
                $router,
                $customer->ip_static,
                $customer->mac_address,
                $customer->interface,
                $customer->username ?? 'Create from Customer Management',
                $traceId
            );

            if (isset($createArp['after']['message'])) {
                $this->ipStaticService->deleteIpFromSimpleQueue($router, $simpleQueueId, $traceId);

                $error = "Cannot create ARP entry for IP {$customer->ip_static} on server {$router->name}, {$createArp['after']['message']}.";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'ip_static'   => $customer->ip_static,
                    'server'      => $router->name,
                    'response'    => $createArp,
                ], 400, $error, $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($error);
            }

            $arpId = $createArp['after']['ret'];
            $customer->update([
                'activation'   => true,
                'is_active'    => true,
                'simpleque_id' => $simpleQueueId,
                'arp_id'       => $arpId,
            ]);

            LogHelper::logInfo(null, [
                'customer_id'  => $customer->id,
                'simpleque_id' => $simpleQueueId,
                'arp_id'       => $arpId,
                'server'       => $router->name,
            ], 200, 'IP Static configuration exported', $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess('Successfully exported IP Static configuration for customer');
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'customer_id' => $customer->id,
                    'server'      => $router->name,
                ],
                '[MikrotikExpired] Failed to export IP Static configuration',
                $this->mikrotikHelper->getStatusCode($e),
                $traceId,
                $timer->lap()
            );

            return $this->mikrotikHelper->responseError("Cannot connect to the server {$router->name}.");
        }
    }
}
