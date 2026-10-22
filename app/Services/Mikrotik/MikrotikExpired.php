<?php

namespace App\Services\Mikrotik;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Models\Packages\Package;
use App\Services\Mikrotik\IpStatic\MikrotikExportIpStaticSecret;
use App\Services\Mikrotik\Ppp\MikrotikExportPppSecret;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\QueueService;

class MikrotikExpired
{
    public function __construct(
        private MikrotikComment $mikrotikComment,
        private MikrotikHelper $mikrotikHelper,
        private MikrotikExportIpStaticSecret $mikrotikExportIpStaticSecret,
        private MikrotikExportPppSecret $mikrotikExportPppSecret,
    ) {}

    /**
     * Update secret customer di Mikrotik menjadi Profile Expire.
     */
    public function handle(Customer $customer, Package $package, string $traceId): array
    {
        $timer = new ExecutionTimer();

        try {
            // Eager load relasi mikrotik & pppType sekaligus untuk mencegah N+1 Query
            $customer->loadMissing(['mikrotik', 'pppType']);
            $router = $customer->mikrotik;

            if (!$router || $router->disabled === 'true') {
                $error = 'Server ' . ($router->name ?? $customer->mikrotik_id) . ' is disabled or not found, please enable first.';
                LogHelper::logError(null, ['server' => $router->name ?? null], 400, $error, $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($error);
            }

            $expireProfile = $package->profile;
            if (!$expireProfile) {
                $error = "Package '{$package->name}' has no profile setting.";
                LogHelper::logError(null, ['customer_id' => $customer->id, 'package_id' => $package->id], 400, $error, $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($error);
            }

            $commentPayment = $this->mikrotikComment->getCommentForExpired($customer, $traceId);

            // Eksekusi update berdasarkan tipe layanan internet
            $result = match ($customer->internet_service) {
                'ppp'       => $this->mikrotikExportPppSecret->handle($customer, $router, $expireProfile, $commentPayment, $traceId),
                'ip_static' => $this->mikrotikExportIpStaticSecret->handle($customer, $router, $expireProfile, $commentPayment, $traceId),
                default     => $this->mikrotikHelper->responseError("Unsupported internet service type: {$customer->internet_service}"),
            };

            if (isset($result['error'])) {
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'server'      => $router->name,
                    'service'     => $customer->internet_service,
                ], 400, $result['message'], $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($result['message']);
            }

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'server'      => $router->name,
                'profile'     => $expireProfile,
            ], 200, '[MikrotikExpired] Customer secret updated to Expire successfully', $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess('Successfully updated customer secret to Expire profile');
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'customer_id'     => $customer->id,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                '[MikrotikExpired] Failed to update customer secret to Expired',
                $this->mikrotikHelper->getStatusCode($e),
                $traceId,
                $timer->lap()
            );

            return $this->mikrotikHelper->responseError('Failed to update customer secret to Expire: ' . $e->getMessage());
        }
    }
}
