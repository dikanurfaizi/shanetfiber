<?php

namespace App\Services\Mikrotik;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\AutoIsolir;
use App\Models\Billings\Billing;
use App\Services\Mikrotik\IpStatic\MikrotikPaymentIpStaticSecret;
use App\Services\Mikrotik\Ppp\MikrotikPaymentPppSecret;

class MikrotikPayment
{
    public function __construct(
        private MikrotikPaymentPppSecret $mikrotikPaymentPppSecret,
        private MikrotikPaymentIpStaticSecret $mikrotikPaymentIpStatic,
        private MikrotikHelper $mikrotikHelper
    ) {}

    public function handle(Billing $billing, string $traceId): array
    {
        $timer = new ExecutionTimer();

        try {
            $billing->loadMissing(['customer.mikrotik']);
            $customer = $billing->customer;

            if (!$customer) {
                LogHelper::logError(null, ['billing_id' => $billing->id], "Customer not found for billing", $traceId, $timer->lap());
                return $this->mikrotikHelper->responseError("Customer not found for billing");
            }

            $router = $customer->mikrotik;

            if (!$router || $router->disabled === 'true') {
                LogHelper::logError(null, ['router_id' => $customer->mikrotik_id], "Router not found or disabled", $traceId, $timer->lap());
                return $this->mikrotikHelper->responseError("Router not found or disabled");
            }

            $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            if (!$autoIsolir || $autoIsolir->disabled === 'true') {
                LogHelper::logError(null, ['mikrotik_id' => $customer->mikrotik_id], "AutoIsolir not found or disabled", $traceId, $timer->lap());
                return $this->mikrotikHelper->responseError("AutoIsolir not found or disabled");
            }

            return match ($customer->internet_service) {
                'ppp'       => $this->mikrotikPaymentPppSecret->handle($router, $billing, $traceId),
                'ip_static' => $this->mikrotikPaymentIpStatic->handle($router, $billing, $autoIsolir, $traceId),
                default     => $this->mikrotikHelper->responseError("Unknown internet service type: {$customer->internet_service}"),
            };
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                ['billing_id' => $billing->id, 'trace' => $e->getTraceAsString()],
                "processPaymentActions exception: " . $e->getMessage(),
                $traceId,
                $timer->lap()
            );

            return $this->mikrotikHelper->responseError("Payment action failed: " . $e->getMessage());
        }
    }
}
