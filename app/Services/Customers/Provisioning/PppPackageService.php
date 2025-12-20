<?php

namespace App\Services\Customers\Provisioning;

use App\Helpers\LogHelper;
use App\Http\Resources\Pppoe\SecretResource;
use App\Models\AutoIsolir;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Services\Routerboard\Ppp\SecretService;
use RuntimeException;

class PppPackageService
{
    public function __construct(
        private SecretService $secretService
    ) {}

    public function applyPackageChange(
        Customer $customer,
        Mikrotik $router,
        ?AutoIsolir $autoIsolir,
        string $profile,
        string $traceId
    ): array {
        // // Guard tambahan: kalau config tidak mengizinkan, jangan sentuh Mikrotik.
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logInfo(
                null,
                [
                    'service'     => 'CustomerUpdateService',
                    'customer_id' => $customer->id,
                    'router_id'   => $router->id,
                    'note'        => 'Skipped PPP package update because allow_mikrotik_actions = false',
                ],
                200,
                'PPP package update skipped by configuration',
                $traceId
            );

            return [
                'success'     => true,
                'customer_id' => $customer->id,
                'new_profile' => $profile
            ];
        }

        try {
            LogHelper::logInfo(null, [
                'service'     => 'CustomerUpdateService',
                'customer_id' => $customer->id,
                'secret_id'   => $customer->secret_id,
                'router_id'   => $router->id,
            ], 200, 'Start updating PPP package', $traceId);

            // Step 1: Ambil secret
            $userSecret = $this->secretService->getSecretById($router, $customer->secret_id, $traceId);

            if ($userSecret === 'error') {
                throw new RuntimeException("Cannot find user {$customer->username} on server");
            }

            // Step 2: Ambil profile dari secret
            $secretProfiles = SecretResource::collection($userSecret)->value('profile');

            // Step 3: Update profile jika berbeda
            if (!$autoIsolir || $autoIsolir->profile_id != $secretProfiles) {
                $response = $this->secretService->updateProfileSecret(
                    $router,
                    $profile,
                    $customer->secret_id,
                    $traceId
                );

                if (isset($response['after']['message'])) {
                    throw new RuntimeException($response['after']['message']);
                }

                // Step 4: Hapus active secret
                $this->secretService->deleteActiveSecret($router, $customer->username, $traceId);
            }

            LogHelper::logInfo(null, [
                'service'     => 'CustomerUpdateService',
                'customer_id' => $customer->id,
                'router_id'   => $router->id,
                'new_profile' => $profile
            ], 200, 'PPP package updated successfully', $traceId);

            return [
                'success'     => true,
                'customer_id' => $customer->id,
                'new_profile' => $profile
            ];
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'service'     => 'CustomerUpdateService',
                'exception'   => [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine()
                ],
                'customer_id' => $customer->id,
                'router_id'   => $router->id
            ], 500, 'Failed to update PPP package', $traceId);

            return [
                'success'     => false,
                'customer_id' => $customer->id,
                'new_profile' => $profile
            ];
        }
    }
}
