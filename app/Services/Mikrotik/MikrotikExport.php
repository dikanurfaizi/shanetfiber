<?php

namespace App\Services\Mikrotik;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Models\Packages\Package;
use App\Services\Mikrotik\IpStatic\MikrotikExportIpStaticSecret;
use App\Services\Mikrotik\Ppp\MikrotikExportPppSecret;

class MikrotikExport
{
    public function __construct(
        private MikrotikComment $mikrotikComment,
        private MikrotikHelper $mikrotikHelper,
        private MikrotikExportIpStaticSecret $mikrotikExportIpStaticSecret,
        private MikrotikExportPppSecret $mikrotikExportPppSecret
    ) {}

    public function handle(Customer $customer, string $traceId): array
    {
        $timer = new ExecutionTimer();
        $mikrotikId = $customer->mikrotik_id;

        try {
            $router = Mikrotik::find($mikrotikId);

            if (!$router || $router->disabled === 'true') {
                $error = 'Server ' . ($router->name ?? $mikrotikId) . ' is disabled or not found, please enable first.';
                LogHelper::logError(null, ['server' => $router->name ?? null], 400, $error, $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($error);
            }

            // Ambil subscription berdasarkan logic fallback
            $subscription = $customer->currentSubscription();

            if (!$subscription) {
                $error = 'No subscription found for customer ID: ' . $customer->id;
                LogHelper::logError(null, ['customer_id' => $customer->id], 400, $error, $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($error);
            }

            // --- Logic Penentuan Profile & Comment ---
            if ($subscription->status === 'active') {
                // Pelanggan Aktif: Gunakan profile paket bawaan dan comment aktivasi/pembayaran
                $profile = $subscription->package?->profile;
                $commentPayment = $this->mikrotikComment->getCommentForActivation($customer, $traceId);
            } else {
                // Pelanggan Tidak Aktif (Expired/Isolir): Gunakan profile 'Expire' dan comment expired
                $profile = Package::where('name', 'Expire')->value('profile');
                $commentPayment = $this->mikrotikComment->getCommentForExpired($customer, $traceId);

                if (!$profile) {
                    $error = "Package 'Expire' not found or has no profile setting.";
                    LogHelper::logError(null, ['customer_id' => $customer->id], 400, $error, $traceId, $timer->lap());

                    return $this->mikrotikHelper->responseError($error);
                }
            }

            return match ($customer->internet_service) {
                'ppp'       => $this->mikrotikExportPppSecret->handle($customer, $router, $profile, $commentPayment, $traceId),
                'ip_static' => $this->mikrotikExportIpStaticSecret->handle($customer, $router, $profile, $commentPayment, $traceId),
                default     => $this->mikrotikHelper->responseError("Unsupported internet service type: {$customer->internet_service}"),
            };
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
                '[MikrotikExport] Failed to export customer secret',
                $this->mikrotikHelper->getStatusCode($e),
                $traceId,
                $timer->lap()
            );

            return $this->mikrotikHelper->responseError($e->getMessage());
        }
    }
}
