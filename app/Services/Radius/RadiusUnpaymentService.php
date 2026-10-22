<?php

namespace App\Services\Radius;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\AutoIsolir;
use App\Models\Billings\Billing;
use App\Models\Websystem;
use App\Services\Mikrotik\MikrotikHelper;
use App\Services\Mikrotik\MikrotikUnpayment;
use App\Services\Routerboard\Ppp\SecretService;
use Exception;

class RadiusUnpaymentService
{
    public function __construct(
        private RadiusUserService $radiusUserService,
        private MikrotikUnpayment $mikrotikUnpayment,
        private MikrotikHelper $mikrotikHelper
    ) {}

    public function handle(Billing $billing, string $traceId): array
    {
        $timer = new ExecutionTimer();

        $billing->loadMissing(['customer.activeSubscription.package.pppProfile', 'customer.mikrotik']);
        $customer = $billing->customer;

        if (!$customer) {
            return $this->mikrotikHelper->responseError("Customer missing for billing");
        }

        try {
            $webSystem = Websystem::first();
            $driver = $webSystem?->provisioning_driver ?? 'both';

            $router = $customer->mikrotik;
            $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();
            $username = $customer->username;

            $day = (int) now()->format('j');

            // Penentuan Due Date berdasarkan status Billing
            if ($billing->status === 'PL' && !empty($billing->paylater_until)) {
                // 1. Jika Paylater (PL), gunakan tanggal batas paylater
                $dueDate = (int) now()->parse($billing->paylater_until)->format('j');
            } elseif (!empty($billing->due_date)) {
                // 2. Jika tanggal jatuh tempo spesifik ada di Billing (BL/Normal)
                $dueDate = (int) now()->parse($billing->due_date)->format('j');
            } else {
                // 3. Fallback: Menggunakan acuan AutoIsolir / Customer Activation Date
                $dueDate = (int) ($autoIsolir?->activation_date === 'false'
                    ? ($autoIsolir->due_date ?? 16)
                    : now()->parse($customer->activation_date)->format('j'));
            }

            if ($day <= $dueDate) {
                // 1. Sebelum Jatuh Tempo: Kembalikan group di FreeRADIUS ke profil normal
                $normalProfile = $customer->activeSubscription?->package?->pppProfile;
                $this->radiusUserService->unisolateUser($username, $normalProfile);

                $actionType = 'reverted_to_normal_group';
            } else {
                // 2. Setelah Jatuh Tempo: Update database FreeRADIUS
                $expireProfile = $autoIsolir?->profile?->profile_name ?? 'Expire';
                $this->radiusUserService->isolateUser($username, $expireProfile);

                // GUARD: Hanya putus koneksi di sini JIKA driver murni 'radius'
                // Jika 'both' atau 'mikrotik', pemutusan koneksi sudah ditangani oleh MikrotikUnpayment
                if ($driver === 'radius' && $router && !empty($username)) {
                    $this->mikrotikUnpayment->handle($billing, $traceId);
                    $actionType = 'moved_to_expire_group_and_session_kicked';
                } else {
                    $actionType = 'moved_to_expire_group_only';
                }
            }

            LogHelper::logInfo('RadiusUnpaymentService', [
                'customer_id' => $customer->id,
                'username'    => $username,
                'driver'      => $driver,
                'action_type' => $actionType,
            ], 200, "FreeRADIUS unpayment processed successfully", $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess("FreeRADIUS unpayment processed successfully ({$actionType})");
        } catch (Exception $e) {
            LogHelper::logError('RadiusUnpaymentService', [
                'billing_id'  => $billing->id,
                'customer_id' => $customer->id,
            ], "processRadiusUnpayment exception: " . $e->getMessage(), $traceId, $timer->lap());

            return $this->mikrotikHelper->responseError($e->getMessage());
        }
    }
}
