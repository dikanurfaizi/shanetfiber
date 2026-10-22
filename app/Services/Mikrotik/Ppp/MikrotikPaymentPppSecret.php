<?php

namespace App\Services\Mikrotik\Ppp;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Http\Resources\Pppoe\SecretResource;
use App\Models\Billings\Billing;
use App\Models\Mikrotik;
use App\Services\Mikrotik\MikrotikComment;
use App\Services\Mikrotik\MikrotikHelper;
use App\Services\Routerboard\Ppp\SecretService;
use Exception;

class MikrotikPaymentPppSecret
{
    public function __construct(
        private SecretService $secretService,
        private MikrotikComment $mikrotikComment,
        private MikrotikHelper $mikrotikHelper
    ) {}

    public function handle(Mikrotik $router, Billing $billing, string $traceId): array
    {
        $timer = new ExecutionTimer();

        $billing->loadMissing('customer.activeSubscription.package');
        $customer = $billing->customer;

        if (!$customer) {
            LogHelper::logError(null, [
                'billingId' => $billing->id,
                'message'   => 'customer_missing'
            ], 404, "Customer missing for billing", $traceId, $timer->lap());

            return $this->mikrotikHelper->responseError("Customer missing for billing");
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
            $userSecretId = $this->secretService->getSecretById($router, $customer->secret_id, $traceId);

            if ($userSecretId === 'error') {
                return $this->mikrotikHelper->responseError("{$customer->secret_id}! Cannot find user \"{$customer->username}\" on server {$router->name}.");
            }

            $secretProfiles = SecretResource::collection($userSecretId)->value('profile');
            $customerProfile = $customer->activeSubscription?->package?->profile;

            $commentPayment = $this->mikrotikComment->getCommentForPayment($billing, $traceId);

            if ($secretProfiles === $customerProfile) {
                // Profil sama: Hanya perbarui komentar pembayaran
                $this->secretService->addCommentSecret($router, $commentPayment, $customer->secret_id, $traceId);
                $actionType = 'updated_comment_only';
            } else {
                // Profil berbeda (misal dari Expire ke Normal): Putus koneksi aktif, update profil, lalu perbarui komentar
                $this->secretService->deleteActiveSecret($router, $customer->username, $traceId);
                $this->secretService->updateProfileSecret($router, $customerProfile, $customer->secret_id, $traceId);
                $this->secretService->addCommentSecret($router, $commentPayment, $customer->secret_id, $traceId);
                $actionType = 'updated_profile_and_comment';
            }

            LogHelper::logInfo(null, [
                'customer_id'      => $customer->id,
                'customer_profile' => $customerProfile,
                'internet_service' => $customer->internet_service,
                'comment_payment'  => $commentPayment,
                'action_type'      => $actionType,
            ], 200, "Process PPP payment patched successfully", $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess("Process PPP payment patched successfully");
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'router'      => $router->name,
                'customer_id' => $customer->id
            ], "processPppPayment exception: " . $e->getMessage(), $traceId, $timer->lap());

            return $this->mikrotikHelper->responseError("Server {$router->name} offline or unreachable: {$e->getMessage()}");
        }
    }
}
