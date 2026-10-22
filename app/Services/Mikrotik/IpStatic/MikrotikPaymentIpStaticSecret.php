<?php

namespace App\Services\Mikrotik\IpStatic;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\AutoIsolir;
use App\Models\Billings\Billing;
use App\Models\Mikrotik;
use App\Services\Mikrotik\MikrotikComment;
use App\Services\Mikrotik\MikrotikHelper;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\Ppp\SecretService;
use Exception;

class MikrotikPaymentIpStaticSecret
{
    public function __construct(
        private IpStaticService $ipStaticService,
        private SecretService $secretService,
        private MikrotikHelper $mikrotikHelper,
        private MikrotikComment $mikrotikComment
    ) {}

    public function handle(
        Mikrotik $router,
        Billing $billing,
        AutoIsolir $autoIsolir,
        string $traceId
    ): array {
        $timer = new ExecutionTimer();

        $billing->loadMissing('customer');
        $customer = $billing->customer;

        if (!$customer) {
            LogHelper::logError(null, [
                'billingId'        => $billing->id,
                'billingPaymentId' => $billing->billing_payment_id,
                'message'          => 'customer_id_missing'
            ], 404, "Customer ID Missing for billing", $traceId, $timer->lap());

            return $this->mikrotikHelper->responseError("Customer ID Missing for billing");
        }

        // Guard Check: Jika action MikroTik di-disable via config
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'router'      => $router->name,
            ], 200, "Mikrotik action skipped by config (allow_mikrotik_actions = false)", $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess("Skipped Mikrotik API execution (action disabled by config)");
        }

        try {
            $ipAddress = $customer->ip_static;
            $simplequeId = $customer->simpleque_id;
            $addressListIsolir = $autoIsolir->address_list_isolir;
            $commentPayment = $this->mikrotikComment->getCommentForPayment($billing, $traceId);
            $comment = $customer->username . '_' . $commentPayment;

            $addressListCustomer = $this->ipStaticService->getAddressListByIpAddress($router, $ipAddress, $traceId);
            $currentList = $addressListCustomer[0]['list'] ?? null;

            if ($currentList === $addressListIsolir) {
                // Eksekusi langsung jika terisolir (Hapus dari Address List Isolir + Update Komentar Queue)
                $this->ipStaticService->deleteIpFromAddressList($router, $ipAddress, $addressListIsolir, $traceId);
                $this->ipStaticService->setCommentSimpleque($router, $simplequeId, $comment, $traceId);
                $actionType = 'unisolated_and_updated_comment';
            } else {
                // Eksekusi langsung jika pembayaran normal sebelum jatuh tempo
                $this->ipStaticService->setCommentSimpleque($router, $simplequeId, $comment, $traceId);
                $actionType = 'updated_comment';
            }

            LogHelper::logInfo(null, [
                'customer_id'           => $customer->id,
                'customer_name'         => $customer->name,
                'ip_address'            => $ipAddress,
                'simple_queue_id'       => $simplequeId,
                'address_list_isolir'   => $addressListIsolir,
                'address_list_customer' => $addressListCustomer,
                'internet_service'      => $customer->internet_service,
                'comment_payment'       => $comment,
                'action_type'           => $actionType,
            ], 200, "Successfully executed IP Static payment sync", $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess("Successfully executed IP Static payment sync");
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'router'      => $router->name,
                'customer_id' => $customer->id
            ], "processIpStaticPayment exception: " . $e->getMessage(), $traceId, $timer->lap());

            return $this->mikrotikHelper->responseError("Cannot connect to Server {$router->name}: {$e->getMessage()}");
        }
    }
}
