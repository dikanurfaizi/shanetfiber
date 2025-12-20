<?php

namespace App\Services\Customers\Provisioning;

use App\Helpers\LogHelper;
use App\Models\Packages\Package;
use App\Services\Routerboard\TestConnectionService;
use PHPUnit\Metadata\Test;

class ChangePackageService
{
    private TestConnectionService $testConnectionService;
    private PppPackageService $pppPackageService;
    private IpStaticPackageService $ipStaticPackageService;

    public function __construct(
        TestConnectionService $testConnectionService,
        PppPackageService $pppPackageService,
        IpStaticPackageService $ipStaticPackageService
    ) {
        $this->testConnectionService = $testConnectionService;
        $this->pppPackageService = $pppPackageService;
        $this->ipStaticPackageService = $ipStaticPackageService;
    }

    public function applyPackageChange($customer, $oldSubscription, $newSubscription, $traceId)
    {
        // === STEP 2: Mikrotik — hanya dijalankan jika diizinkan oleh config ===
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logInfo(
                null,
                ['config_key' => config('services.shanetfiber.allow_mikrotik_actions')],
                200,
                'Mikrotik actions skipped by configuration flag',
                $traceId
            );

            return [
                'success' => true,
                'message' => 'switch package done (DB only, Mikrotik actions skipped by config)',
                'traceId' => $traceId
            ];
        }

        try {
            // Pastikan router online sebelum kirim perubahan
            $this->testConnectionService->checkOnline($customer->mikrotik, $traceId);
        } catch (\Exception $e) {
            $message = 'Router offline during package change';
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine()
                    ],
                    'router_id' => $customer->mikrotik->id
                ],
                503,
                $message,
                $traceId
            );

            // DB sudah terupdate, tapi Mikrotik gagal — sengaja di-signal sebagai error.
            return [
                'success' => false,
                'message' => $message,
                'traceId' => $traceId
            ];
        }

        $oldPackage = $oldSubscription->package;
        $oldPackageName = $oldPackage?->name ?? 'unknown';
        $newPackage = $newSubscription->package;
        $newPackageName = $newPackage?->name;
        $profile    = $newPackage?->profile;

        // Update Mikrotik profile
        if ($customer->internet_service === 'ppp') {
            $result = $this->pppPackageService->applyPackageChange(
                $customer,
                $customer->mikrotik,
                $customer->autoIsolir,
                $profile,
                $traceId
            );

            if ($result['success'] === false) {
                $message = "Failed to update Ppp package from {$oldPackageName} to {$newPackageName} (DB already updated)";
                LogHelper::logError(
                    null,
                    [
                        'service'       => 'CustomerUpdateService',
                        'customer_id'   => $customer->id,
                        'customer_name' => $customer->name,
                        'old_package'   => $oldPackageName,
                        'new_package'   => $newPackageName,
                        'router_note'   => 'DB updated, Mikrotik profile not updated',
                        'network_side'  => $result ?? null,
                    ],
                    500,
                    $message,
                    $traceId
                );

                return [
                    'success' => false,
                    'message' => $message,
                    'traceId' => $traceId
                ];
            }

            return ['success' => true];
        } elseif ($customer->internet_service === 'ip_static') {
            $result = $this->ipStaticPackageService->applyPackageChange(
                $customer,
                $customer->mikrotik,
                $profile,
                $traceId
            );

            if ($result['success'] === false) {
                $message = "Failed to update IpStatic package from {$oldPackageName} to {$newPackageName} (DB already updated)";
                LogHelper::logError(
                    null,
                    [
                        'service'       => 'CustomerUpdateService',
                        'customer_id'   => $customer->id,
                        'customer_name' => $customer->name,
                        'old_package'   => $oldPackageName,
                        'new_package'   => $newPackageName,
                        'router_note'   => 'DB updated, Mikrotik profile not updated',
                        'network_side'  => $result ?? null,
                    ],
                    500,
                    $message,
                    $traceId
                );

                return [
                    'success' => false,
                    'message' => $message,
                    'traceId' => $traceId
                ];
            }

            return ['success' => true];
        }
    }
}
