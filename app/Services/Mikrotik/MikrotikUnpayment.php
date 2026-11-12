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

        LogHelper::logInfo('MikrotikUnpayment', [
            'billing_id' => $billing->id,
            'customer_id' => $customer?->id,
        ], 200, 'Starting MikrotikUnpayment process', $traceId, $timer->lap());

        if (!$customer) {
            LogHelper::logWarning('MikrotikUnpayment', [
                'billing_id' => $billing->id,
            ], 404, 'Customer missing for billing', $traceId, $timer->lap());

            return $this->mikrotikHelper->responseError("Customer missing for billing");
        }

        // Guard Check: Pengecekan config paling akhir sebelum hit API MikroTik
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logInfo('MikrotikUnpayment', [
                'billing_id' => $billing->id,
                'customer_id' => $customer->id,
            ], 200, 'Mikrotik action skipped by config (allow_mikrotik_actions = false)', $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess("Skipped Mikrotik API execution (action disabled by config)");
        }

        try {
            $router = $customer->mikrotik;
            $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            if (!$router || !$autoIsolir || $router->disabled === 'true' || $autoIsolir->disabled === 'true') {
                LogHelper::logWarning('MikrotikUnpayment', [
                    'billing_id' => $billing->id,
                    'customer_id' => $customer->id,
                    'mikrotik_id' => $customer->mikrotik_id,
                    'router_exists' => (bool)$router,
                    'auto_isolir_exists' => (bool)$autoIsolir,
                    'router_disabled' => $router?->disabled,
                    'auto_isolir_disabled' => $autoIsolir?->disabled,
                ], 400, 'Router or AutoIsolir not found or disabled', $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError("Router or AutoIsolir not found or disabled");
            }

            // Gunakan MikrotikComment terpusat untuk Unpayment/Expired
            $commentUnpayment = $this->mikrotikComment->getCommentForExpired($customer, $traceId);

            if ($customer->internet_service === 'ppp') {
                LogHelper::logInfo('MikrotikUnpayment', [
                    'billing_id' => $billing->id,
                    'customer_id' => $customer->id,
                    'service' => 'ppp',
                ], 200, 'Executing PPP unpayment secret service', $traceId, $timer->lap());

                return $this->mikrotikUnpaymentPppSecret->handle($router, $customer, $commentUnpayment, $autoIsolir, $traceId);
            }

            if ($customer->internet_service === 'ip_static') {
                LogHelper::logInfo('MikrotikUnpayment', [
                    'billing_id' => $billing->id,
                    'customer_id' => $customer->id,
                    'service' => 'ip_static',
                ], 200, 'Executing IP Static unpayment secret service', $traceId, $timer->lap());

                return $this->mikrotikUnpaymentIpStaticSecret->handle($router, $customer, $commentUnpayment, $autoIsolir, $traceId);
            }

            LogHelper::logWarning('MikrotikUnpayment', [
                'billing_id' => $billing->id,
                'customer_id' => $customer->id,
                'internet_service' => $customer->internet_service,
            ], 400, "Unknown internet service: {$customer->internet_service}", $traceId, $timer->lap());

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
