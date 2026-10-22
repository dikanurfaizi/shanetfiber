<?php

namespace App\Services\Mikrotik;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\AutoIsolir;
use App\Models\Billings\Billing;
use App\Services\Mikrotik\IpStatic\MikrotikUnpaymentIpStaticSecret;
use App\Services\Mikrotik\Ppp\MikrotikUnpaymentPppSecret;
use Exception;

class MikrotikUnpayment
{
    public function __construct(
        private MikrotikUnpaymentIpStaticSecret $mikrotikUnpaymentIpStaticSecret,
        private MikrotikUnpaymentPppSecret $mikrotikUnpaymentPppSecret,
        private MikrotikComment $mikrotikComment,
        private MikrotikHelper $mikrotikHelper
    ) {}

    public function handle(Billing $billing, string $traceId): array
    {
        $timer = new ExecutionTimer();

        $billing->loadMissing(['customer.mikrotik']);
        $customer = $billing->customer;

        if (!$customer) {
            return $this->mikrotikHelper->responseError("Customer missing for billing");
        }

        // Guard Check: Pengecekan config paling akhir sebelum hit API MikroTik
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logInfo('MikrotikUnpayment', [
                'customer_id' => $customer->id,
            ], 200, 'Mikrotik action skipped by config (allow_mikrotik_actions = false)', $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess("Skipped Mikrotik API execution (action disabled by config)");
        }

        try {
            $router = $customer->mikrotik;
            $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            if (!$router || !$autoIsolir || $router->disabled === 'true' || $autoIsolir->disabled === 'true') {
                return $this->mikrotikHelper->responseError("Router or AutoIsolir not found or disabled");
            }

            // Gunakan MikrotikComment terpusat untuk Unpayment/Expired
            $commentUnpayment = $this->mikrotikComment->getCommentForExpired($customer, $traceId);

            if ($customer->internet_service === 'ppp') {
                return $this->mikrotikUnpaymentPppSecret->handle($router, $customer, $commentUnpayment, $autoIsolir, $traceId);
            }

            if ($customer->internet_service === 'ip_static') {
                return $this->mikrotikUnpaymentIpStaticSecret->handle($router, $customer, $commentUnpayment, $autoIsolir, $traceId);
            }

            return $this->mikrotikHelper->responseError("Unknown internet service: {$customer->internet_service}");
        } catch (Exception $e) {
            LogHelper::logError('MikrotikUnpayment', [
                'billing_id' => $billing->id,
                'customer_id' => $customer->id,
            ], "processUnpayment exception: " . $e->getMessage(), $traceId, $timer->lap());

            return $this->mikrotikHelper->responseError($e->getMessage());
        }
    }
}
