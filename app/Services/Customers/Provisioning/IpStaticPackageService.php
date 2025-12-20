<?php

namespace App\Services\Customers\Provisioning;

use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\QueueService;
use RuntimeException;

class IpStaticPackageService
{
    public function __construct(
        private IpStaticService $ipStaticService,
        private SecretService $secretService,
        private QueueService $queueService
    ) {}

    public function applyPackageChange(Customer $customer, Mikrotik $router, string $profile, string $traceId): array
    {
        // Guard tambahan: kalau config tidak mengizinkan, jangan sentuh Mikrotik.
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logInfo(
                null,
                [
                    'service'     => 'CustomerUpdateService',
                    'customer_id' => $customer->id,
                    'router_id'   => $router->id,
                    'note'        => 'Skipped IP Static package update because allow_mikrotik_actions = false',
                ],
                200,
                'IP Static package update skipped by configuration',
                $traceId
            );

            return [
                'success'     => true,
                'customer_id' => $customer->id,
                'new_profile' => $profile
            ];
        }

        try {
            $ipAddress = $this->ipStaticService->getIpByArpId($router, $customer->arp_id, $traceId);

            if ($ipAddress === 'error') {
                throw new RuntimeException("Cannot find ARP entry for customer");
            }

            $profileData = $this->secretService->getPppProfileByName($router, $profile, $traceId)[0];
            $rateLimit   = $profileData['rate-limit'] ?? '';

            if (empty($rateLimit)) {
                throw new RuntimeException("Rate limit not configured for profile {$profile}");
            }

            $parts = explode(' ', $rateLimit);
            $this->queueService->setLimitSimplequeById(
                $router,
                $customer->simpleque_id,
                $profileData['parent-queue'] ?? 'none',
                "{$parts[4]}/{$parts[4]}",
                $profileData['queue-type'] ?? 'default-small',
                $parts[5],
                $parts[0],
                $traceId
            );

            LogHelper::logInfo(
                null,
                [
                    'service'     => 'CustomerUpdateService',
                    'customer_id' => $customer->id,
                    'new_profile' => $profile,
                    'rate_limit'  => $rateLimit
                ],
                200,
                'IP Static package updated successfully',
                $traceId
            );

            return [
                'success'     => true,
                'customer_id' => $customer->id,
                'new_profile' => $profile
            ];
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'service'     => 'CustomerUpdateService',
                    'exception'   => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'router_id'   => $router->id
                ],
                500,
                'Failed to update IP Static package',
                $traceId
            );

            return [
                'success'     => false,
                'customer_id' => $customer->id,
                'new_profile' => $profile
            ];
        }
    }
}
