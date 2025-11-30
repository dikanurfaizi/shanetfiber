<?php

namespace App\Services\Customers;

use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Models\Packages\Package;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\CustomerSubscription;
use App\Models\Billing\BillingSubscriptionPeriode;
use App\Models\SubscriptionDuration;
use App\Models\Websystem;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Jobs\AddPaymentIpStaticBeforeDueDateJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\QueueService;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\TestConnectionService;
use App\Services\Billings\BillingService;
use App\Http\Resources\Pppoe\SecretResource;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use App\Events\PackageChangesApplied;

class CustomerUpdateService
{
    public function __construct(
        private SecretService $secretService,
        private QueueService $queueService,
        private IpStaticService $ipStaticService,
        private TestConnectionService $testConnectionService,
        private BillingService $billingService
    ) {}

    public function updateCustomer(Customer $customer, array $validatedData, string $traceId, string $action): array
    {
        try {
            LogHelper::logInfo(
                null,
                [
                    'service'         => 'CustomerUpdateService',
                    'customer_id'     => $customer->id,
                    'customer_name'   => $customer->name,
                    'action'          => $action,
                    'validated_keys'  => array_keys($validatedData),
                ],
                200,
                'Starting customer update process',
                $traceId
            );

            $message = '';

            // === CASE 1: Change Package ===
            if ($action === 'change_package') {
                LogHelper::logInfo(
                    null,
                    [
                        'service'        => 'CustomerUpdateService',
                        'customer_id'    => $customer->id,
                        'customer_name'  => $customer->name,
                        'old_package_id' => $customer->package_id,
                        'new_package_id' => $validatedData['package_id'] ?? null,
                    ],
                    200,
                    'Initiating package change validation',
                    $traceId
                );

                // Validasi pergantian package (murni DB read)
                $result = $this->validatePackageChange($customer, $traceId);

                if (!$result['success']) {
                    LogHelper::logWarning(
                        null,
                        [
                            'service'        => 'CustomerUpdateService',
                            'customer_id'    => $customer->id,
                            'customer_name'  => $customer->name,
                            'old_package_id' => $customer->package_id,
                            'new_package_id' => $validatedData['package_id'] ?? null,
                            'reason'         => $result['message'],
                        ],
                        400,
                        'Package change validation failed',
                        $traceId
                    );
                    return $result;
                }

                LogHelper::logInfo(
                    null,
                    [
                        'service'        => 'CustomerUpdateService',
                        'customer_id'    => $customer->id,
                        'customer_name'  => $customer->name,
                        'old_package_id' => $customer->package_id,
                        'new_package_id' => $validatedData['package_id'] ?? null,
                    ],
                    200,
                    'Package validation passed, applying change',
                    $traceId
                );

                // Jalankan perubahan package:
                // 1) Switch subscription & update DB
                // 2) Baru kemudian update Mikrotik (dengan env guard)
                $changesResult = $this->handlePackageChange($customer, $validatedData, $traceId);

                if (!$changesResult['success']) {
                    LogHelper::logError(
                        null,
                        [
                            'service'        => 'CustomerUpdateService',
                            'customer_id'    => $customer->id,
                            'customer_name'  => $customer->name,
                            'old_package_id' => $customer->package_id,
                            'new_package_id' => $validatedData['package_id'] ?? null,
                            'reason'         => $changesResult['message'],
                        ],
                        400,
                        'Package change failed during update',
                        $traceId
                    );
                    return $changesResult;
                }

                $message = $changesResult['message'];

                LogHelper::logInfo(
                    null,
                    [
                        'service'        => 'CustomerUpdateService',
                        'customer_id'    => $customer->id,
                        'customer_name'  => $customer->name,
                        'old_package_id' => $customer->package_id,
                        'new_package_id' => $validatedData['package_id'] ?? null,
                        'package_change' => true,
                    ],
                    200,
                    'Customer package updated successfully',
                    $traceId
                );
            }

            // === CASE 2: Normal Update ===
            else {
                LogHelper::logInfo(
                    null,
                    [
                        'service'       => 'CustomerUpdateService',
                        'customer_id'   => $customer->id,
                        'customer_name' => $customer->name,
                        'action'        => $action,
                    ],
                    200,
                    'Processing standard customer update (activation/member/data)',
                    $traceId
                );

                // Flow normal: activation date, data, member/billing
                // Di sini belum dibuat 1 transaksi besar (opsi A = ringan),
                // tapi semua update DB tetap ter-log rapi.
                $this->handleActivationDateChange($customer, $validatedData, $traceId);
                $this->updateCustomerData($customer, $validatedData, $traceId);
                $this->handleMemberChange($customer, $validatedData, $traceId);

                $message = 'Customer updated successfully';

                LogHelper::logInfo(
                    null,
                    [
                        'service'        => 'CustomerUpdateService',
                        'customer_id'    => $customer->id,
                        'customer_name'  => $customer->name,
                        'updated_fields' => array_keys($validatedData),
                    ],
                    200,
                    'Customer data and related entities updated successfully',
                    $traceId
                );
            }

            // === FINAL SUCCESS LOG ===
            LogHelper::logInfo(
                null,
                [
                    'service'       => 'CustomerUpdateService',
                    'customer_id'   => $customer->id,
                    'customer_name' => $customer->name,
                    'action'        => $action,
                    'status'        => 'success',
                ],
                200,
                $message,
                $traceId
            );

            return [
                'success' => true,
                'message' => $message,
                'traceId' => $traceId,
            ];
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'service'       => 'CustomerUpdateService',
                    'exception'     => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ],
                    'customer_id'   => $customer->id ?? null,
                    'customer_name' => $customer->name ?? null,
                    'validated_data' => $validatedData,
                    'action'        => $action,
                ],
                500,
                'Failed to update customer',
                $traceId
            );

            return [
                'success' => false,
                'message' => 'Failed to update customer. Please contact IT Helpdesk.',
                'traceId' => $traceId,
            ];
        }
    }

    private function validatePackageChange(Customer $customer, string $traceId): array
    {
        $startTime = microtime(true);

        $log = function (string $level, int $status, string $message, array $context = []) use ($traceId, $customer, $startTime) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2) . ' ms';
            $context = array_merge([
                'service'        => 'CustomerUpdateService',
                'customer_id'    => $customer->id,
                'customer_name'  => $customer->name,
                'execution_time' => $executionTime
            ], $context);

            match ($level) {
                'info'    => LogHelper::logInfo(null, $context, $status, $message, $traceId),
                'warning' => LogHelper::logWarning(null, $context, $status, $message, $traceId),
                'error'   => LogHelper::logError(null, $context, $status, $message, $traceId),
            };
        };

        try {
            // 🔹 Step 1: Ambil periode terakhir
            $lastPeriode = DB::table('billing_subscription_periodes as bsp')
                ->whereIn('bsp.customer_subscription_id', function ($q) use ($customer) {
                    $q->select('id')
                        ->from('customer_subscriptions')
                        ->where('customer_id', $customer->id);
                })
                ->orderByDesc('bsp.created_at')
                ->first();

            if (!$lastPeriode) {
                $log('warning', 404, 'Tidak ada data billing periode');
                return [
                    'success' => false,
                    'message' => 'Package change failed, there is no billing periode',
                    'traceId' => $traceId
                ];
            }

            // Cek apakah periode terakhir sudah dibayar
            if (!$lastPeriode->is_paid) {
                $log('warning', 400, 'Periode terakhir belum dibayar', [
                    'last_periode' => $lastPeriode
                ]);
                return [
                    'success' => false,
                    'message' => 'Package change failed, last period package has not been paid',
                    'traceId' => $traceId
                ];
            }

            // Hitung jumlah periode yang sudah dibayar
            $paidCount = DB::table('billing_subscription_periodes as bsp')
                ->whereIn('bsp.customer_subscription_id', function ($q) use ($customer) {
                    $q->select('id')
                        ->from('customer_subscriptions')
                        ->where('customer_id', $customer->id);
                })
                ->where('bsp.is_paid', true)
                ->count();

            if ($paidCount <= 1) {
                $log('warning', 400, 'Belum berlangganan minimal 1 periode', [
                    'paid_count' => $paidCount
                ]);
                return [
                    'success' => false,
                    'message' => 'Package change failed, have not subscribed for at least 3 periods',
                    'traceId' => $traceId
                ];
            }

            $log('info', 200, 'Validasi billing terpenuhi, lanjut proses ganti package', [
                'paid_count' => $paidCount
            ]);

            return [
                'success' => true,
                'message' => 'Billing validation is fulfilled, continue the package change process',
                'traceId' => $traceId
            ];
        } catch (\Throwable $e) {
            $log('error', 500, 'Unexpected error during validatePackageChange', [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine()
                ]
            ]);
            return [
                'success' => false,
                'message' => 'Unexpected error during change package',
                'traceId' => $traceId
            ];
        }
    }

    /**
     * Refactor penting di sini:
     * - DB (switch subscription + update customer.package_id) dulu.
     * - Setelah DB OK, baru akses Mikrotik (PPP/IP static) dengan env guard.
     */
    private function handlePackageChange(Customer $customer, array $data, string $traceId): array
    {
        $startTime = microtime(true);

        $log = function (string $level, int $status, string $message, array $context = []) use ($traceId, $customer, $startTime) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2) . ' ms';
            $context = array_merge([
                'service'        => 'CustomerUpdateService',
                'customer_id'    => $customer->id,
                'customer_name'  => $customer->name,
                'execution_time' => $executionTime
            ], $context);

            match ($level) {
                'info'    => LogHelper::logInfo(null, $context, $status, $message, $traceId),
                'warning' => LogHelper::logWarning(null, $context, $status, $message, $traceId),
                'error'   => LogHelper::logError(null, $context, $status, $message, $traceId),
            };
        };

        try {
            if (!isset($data['package_id'])) {
                $message = 'Package ID not present in request data';
                $log('info', 200, $message, [
                    'detail' => 'Data package_id tidak dikirim dari form'
                ]);
                return [
                    'success' => false,
                    'message' => $message,
                    'traceId' => $traceId
                ];
            }

            $log('info', 200, 'Checking for package change', [
                'package_id_from_request'  => $data['package_id'],
                'package_id_from_customer' => $customer->package_id
            ]);

            if ((int)$data['package_id'] === (int)$customer->package_id) {
                $message = 'No package change detected';
                $log('info', 200, $message, [
                    'package_id' => $data['package_id']
                ]);
                return [
                    'success' => false,
                    'message' => $message,
                    'traceId' => $traceId
                ];
            }

            // Load router & auto isolir & package lama
            $customer->loadMissing(['mikrotik', 'autoIsolir', 'package']);

            if (!$customer->mikrotik) {
                $message = 'Router not found';
                $log('warning', 404, $message, [
                    'mikrotik_id' => $customer->mikrotik_id
                ]);
                return [
                    'success' => false,
                    'message' => $message,
                    'traceId' => $traceId
                ];
            }

            $log('info', 200, 'Router ditemukan', [
                'mikrotik_id' => $customer->mikrotik_id,
                'router_name' => $customer->mikrotik->name ?? null,
            ]);

            if ($customer->autoIsolir) {
                $log('info', 200, 'AutoIsolir ditemukan', [
                    'mikrotik_id' => $customer->mikrotik_id,
                    'autoIsolir'  => $customer->autoIsolir
                ]);
            } else {
                $log('warning', 404, 'AutoIsolir tidak ditemukan', [
                    'mikrotik_id' => $customer->mikrotik_id
                ]);
            }

            // Ambil package baru dan Profile
            $newPackage = Package::find($data['package_id']);
            $profile    = $newPackage?->profile;

            if (!$newPackage || !$profile) {
                $message = 'Profile or Package not found';
                $log('warning', 404, $message, [
                    'package_id' => $data['package_id']
                ]);
                return [
                    'success' => false,
                    'message' => $message,
                    'traceId' => $traceId
                ];
            }

            $log('info', 200, 'Profile ditemukan dari package', [
                'package_id' => $data['package_id'],
                'profile'    => $profile
            ]);

            if ($customer->mikrotik && $customer->mikrotik->disabled === 'true') {
                $message = 'Router disabled';
                $log('warning', 400, $message, [
                    'router_status' => 'disabled'
                ]);
                return [
                    'success' => false,
                    'message' => $message,
                    'traceId' => $traceId
                ];
            }

            $oldPackage = $customer->package;

            // === STEP 1: DB — switch subscription + update customer.package_id ===
            $switchResult = $this->switchCustomerSubscription($customer, $newPackage->id, $traceId);

            if (!$switchResult['success']) {
                // Di titik ini belum ada perubahan Mikrotik sama sekali.
                return $switchResult;
            }

            $customer->update([
                'package_id' => $newPackage->id
            ]);

            $log('info', 200, 'Customer package_id berhasil diperbarui (DB updated before Mikrotik)', [
                'old_package_id' => $oldPackage?->id,
                'new_package_id' => $newPackage->id
            ]);

            $customer->refresh();

            // Trigger WhatsApp (bukan Mikrotik, jadi aman di sini)
            event(new PackageChangesApplied(
                $customer,
                $oldPackage->name,
                $oldPackage->price,
                $traceId
            ));

            // === STEP 2: Mikrotik — hanya dijalankan jika diizinkan oleh config ===
            if (!config('services.shanetfiber.allow_mikrotik_actions')) {
                $log('info', 200, 'Mikrotik actions skipped by configuration flag', [
                    'config_key' => 'services.shanetfiber.allow_mikrotik_actions'
                ]);

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
                $log('error', 503, $message, [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine()
                    ],
                    'router_id' => $customer->mikrotik->id
                ]);

                // DB sudah terupdate, tapi Mikrotik gagal — sengaja di-signal sebagai error.
                return [
                    'success' => false,
                    'message' => $message,
                    'traceId' => $traceId
                ];
            }

            // Update Mikrotik profile
            if ($customer->internet_service === 'ppp') {
                $result = $this->updatePppPackage(
                    $customer,
                    $customer->mikrotik,
                    $customer->autoIsolir,
                    $profile,
                    $traceId
                );

                $check = $this->ensureProfileUpdate(
                    $result,
                    $customer,
                    'PPP',
                    $oldPackage?->name ?? 'unknown',
                    $newPackage->name,
                    $traceId
                );

                if ($check['success'] === false) {
                    return $check;
                }

                $log('info', 200, 'Send update PPP Package', [
                    'package_id'  => $data['package_id'],
                    'new_profile' => $result['new_profile']
                ]);
            } elseif ($customer->internet_service === 'ip_static') {
                $result = $this->updateIpStaticPackage(
                    $customer,
                    $customer->mikrotik,
                    $profile,
                    $traceId
                );

                $check = $this->ensureProfileUpdate(
                    $result,
                    $customer,
                    'IP Static',
                    $oldPackage?->name ?? 'unknown',
                    $newPackage->name,
                    $traceId
                );

                if ($check['success'] === false) {
                    return $check;
                }

                $log('info', 200, 'Send update IP Static Package', [
                    'package_id'  => $data['package_id'],
                    'new_profile' => $result['new_profile']
                ]);
            }

            return [
                'success' => true,
                'message' => 'switch package done',
                'traceId' => $traceId
            ];
        } catch (\Throwable $e) {
            $message = 'Unexpected error during package changes';
            $log('error', 500, 'Unexpected error during handlePackageChange', [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine()
                ]
            ]);
            return [
                'success' => false,
                'message' => $message,
                'traceId' => $traceId
            ];
        }
    }

    private function ensureProfileUpdate(array $result, Customer $customer, string $service, string $oldPackageName, string $newPackageName, string $traceId): array
    {
        if ($result['success'] === false) {
            $message = "Failed to update {$service} package from {$oldPackageName} to {$newPackageName} (DB already updated)";
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

    private function updatePppPackage(
        Customer $customer,
        Mikrotik $router,
        ?AutoIsolir $autoIsolir,
        string $profile,
        string $traceId
    ): array {
        // Guard tambahan: kalau config tidak mengizinkan, jangan sentuh Mikrotik.
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

            return $this->makeResponse(true, $customer, $profile);
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
                throw new \RuntimeException("Cannot find user {$customer->username} on server");
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
                    throw new \RuntimeException($response['after']['message']);
                }

                // Step 4: Hapus active secret
                $this->secretService->deleteActiveSecret($router, $customer->username, $traceId);
            }

            // Step 5: Return sukses
            return $this->makeResponse(true, $customer, $profile);
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

            return $this->makeResponse(false, $customer, $profile);
        }
    }

    private function updateIpStaticPackage(Customer $customer, Mikrotik $router, string $profile, string $traceId): array
    {
        // Guard tambahan: kalau config tidak mengizinkan, jangan sentuh Mikrotik.
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logInfo(
                null,
                [
                    'service'     => 'CustomerUpdateService',
                    'customer_id' => $customer->id,
                    'router_id'   => $router->id,
                    'note'        => 'Skipped IP Static package update because allow_mikrotik_actions = false',
                ],
                200,
                'IP Static package update skipped by configuration',
                $traceId
            );

            return $this->makeResponse(true, $customer, $profile);
        }

        try {
            $ipAddress = $this->ipStaticService->getIpByArpId($router, $customer->arp_id, $traceId);

            if ($ipAddress === 'error') {
                throw new \RuntimeException("Cannot find ARP entry for customer");
            }

            $profileData = $this->secretService->getPppProfileByName($router, $profile, $traceId)[0];
            $rateLimit   = $profileData['rate-limit'] ?? '';

            if (empty($rateLimit)) {
                throw new \RuntimeException("Rate limit not configured for profile {$profile}");
            }

            $parts = explode(' ', $rateLimit);
            $this->queueService->setLimitSimplequeById(
                $router,
                $customer->simpleque_id,
                $profileData['parent-queue'] ?? 'none',
                "{$parts[4]}/{$parts[4]}",
                $profileData['queue-type'] ?? 'default-small',
                $parts[5],
                $parts[0],
                $traceId
            );

            LogHelper::logInfo(
                null,
                [
                    'service'     => 'CustomerUpdateService',
                    'customer_id' => $customer->id,
                    'new_profile' => $profile,
                    'rate_limit'  => $rateLimit
                ],
                200,
                'IP Static package updated successfully',
                $traceId
            );

            return $this->makeResponse(true, $customer, $profile);
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'service'     => 'CustomerUpdateService',
                    'exception'   => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'router_id'   => $router->id
                ],
                500,
                'Failed to update IP Static package',
                $traceId
            );

            return $this->makeResponse(false, $customer, $profile);
        }
    }

    private function makeResponse(bool $success, Customer $customer, string $profile): array
    {
        return [
            'success'     => $success,
            'customer_id' => $customer->id,
            'new_profile' => $profile
        ];
    }

    private function handleActivationDateChange(Customer $customer, array $data, string $traceId): void
    {
        if (empty($data['activation_date'])) {
            return;
        }

        try {
            $inputActivationDate = Carbon::parse($data['activation_date'])->startOfDay();
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'service'   => 'CustomerUpdateService',
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine()
                    ],
                    'data'      => $data,
                ],
                500,
                'Failed to parse activation date',
                $traceId
            );
            return;
        }

        $currentActivationDate = Carbon::parse($customer->activation_date)->startOfDay();

        if ($inputActivationDate->equalTo($currentActivationDate)) {
            return;
        }

        $router    = Mikrotik::find($customer->mikrotik_id);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

        if (
            !$router ||
            $router->disabled === 'true' ||
            !$autoIsolir ||
            $autoIsolir->disabled === 'true'
        ) {
            LogHelper::logWarning(
                null,
                [
                    'service'           => 'CustomerUpdateService',
                    'customer_id'       => $customer->id,
                    'router_status'     => $router ? ($router->disabled ? 'disabled' : 'enabled') : 'not_found',
                    'auto_isolir_status' => $autoIsolir ? ($autoIsolir->disabled ? 'disabled' : 'enabled') : 'not_found'
                ],
                400,
                'Router/AutoIsolir not available for activation date change',
                $traceId
            );
            return;
        }

        try {
            $lastBillPayment = $this->calculateLastBillPayment($customer, $data['activation_date'], $traceId);

            if ($autoIsolir->activation_date === 'true') {
                $comment = $autoIsolir->comment_unpayment . '_' . $lastBillPayment;

                $success = false;

                if ($customer->internet_service === 'ppp') {
                    $success = $this->handlePppMikrotikUpdate($router, $comment, $customer->secret_id, $traceId);
                } else {
                    $success = $this->handleIpStaticMikrotikUpdate(
                        $router,
                        $customer->ip_static,
                        $customer->simpleque_id,
                        $autoIsolir->address_list_isolir,
                        $comment,
                        $customer->username . '_' . $comment,
                        $traceId
                    );
                }

                if ($success) {
                    $customer->update([
                        'activation_date' => $data['activation_date'],
                    ]);

                    LogHelper::logInfo(
                        null,
                        [
                            'service'            => 'CustomerUpdateService',
                            'customer_id'        => $customer->id,
                            'new_activation_date' => $data['activation_date']
                        ],
                        200,
                        'Activation date updated successfully',
                        $traceId
                    );
                }
            }
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'service'     => 'CustomerUpdateService',
                    'exception'   => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine()
                    ],
                    'customer_id' => $customer->id
                ],
                500,
                'Failed to update activation date',
                $traceId
            );
            throw $e;
        }
    }

    private function handlePppMikrotikUpdate($router, $comment, $secretId, string $traceId): bool
    {
        // Env guard di level helper juga, supaya aman kalau dipakai tempat lain.
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logInfo(
                null,
                [
                    'service'   => 'CustomerUpdateService',
                    'router_id' => $router->id ?? null,
                    'secret_id' => $secretId,
                    'note'      => 'Skipped PPP Mikrotik update because allow_mikrotik_actions = false',
                ],
                200,
                'PPP Mikrotik update skipped by configuration',
                $traceId
            );
            return true;
        }

        try {
            dispatch(new AddPaymentBeforeDueDateJob($router, $comment, $secretId, $traceId));
            return true;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'service'   => 'CustomerUpdateService',
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine()
                    ],
                    'router_id' => $router->id ?? null,
                    'secret_id' => $secretId
                ],
                500,
                'Failed to dispatch PPP update job',
                $traceId
            );
            throw $e;
        }
    }

    private function handleIpStaticMikrotikUpdate($router, $ipStatic, $queueId, $addressList, $comment, $identity, string $traceId): bool
    {
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logInfo(
                null,
                [
                    'service'    => 'CustomerUpdateService',
                    'router_id'  => $router->id ?? null,
                    'ip_static'  => $ipStatic,
                    'queue_id'   => $queueId,
                    'note'       => 'Skipped IP Static Mikrotik update because allow_mikrotik_actions = false',
                ],
                200,
                'IP Static Mikrotik update skipped by configuration',
                $traceId
            );
            return true;
        }

        try {
            dispatch(new AddPaymentIpStaticBeforeDueDateJob(
                $router,
                $ipStatic,
                $queueId,
                $addressList,
                $comment,
                $identity
            ));
            return true;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'service'   => 'CustomerUpdateService',
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine()
                    ],
                    'router_id' => $router->id ?? null,
                    'ip_static' => $ipStatic
                ],
                500,
                'Failed to dispatch IP Static update job',
                $traceId
            );
            throw $e;
        }
    }

    private function calculateLastBillPayment(Customer $customer, string $activationDate, string $traceId): string
    {
        try {
            LogHelper::logInfo(
                null,
                [
                    'service'         => 'CustomerUpdateService',
                    'customer_id'     => $customer->id,
                    'activation_date' => $activationDate,
                    'billing_period'  => $customer->billing_period,
                ],
                200,
                'Starting last bill payment calculation',
                $traceId
            );

            $activationDate   = Carbon::parse($activationDate);
            $totalDaysInMonth = $activationDate->daysInMonth;
            $day              = min($activationDate->day, $totalDaysInMonth);

            // === Fetch last billing records ===
            $lastPayment = Billing::where('customer_id', $customer->id)
                ->where('status', 'LS') // Lunas
                ->latest('payment_time')
                ->first();

            $lastPayLater = Billing::where('customer_id', $customer->id)
                ->where('status', 'PL') // Pay Later
                ->latest('pay_later')
                ->first();

            LogHelper::logInfo(
                null,
                [
                    'service'       => 'CustomerUpdateService',
                    'customer_id'   => $customer->id,
                    'last_payment'  => $lastPayment?->payment_time,
                    'last_paylater' => $lastPayLater?->pay_later,
                ],
                200,
                'Fetched last payment and pay-later records',
                $traceId
            );

            // === Determine reference date ===
            if ($lastPayLater && $lastPayment) {
                $referenceDate = $lastPayLater->pay_later > $lastPayment->payment_time
                    ? $lastPayLater->pay_later
                    : $lastPayment->payment_time;

                LogHelper::logInfo(
                    null,
                    [
                        'service'        => 'CustomerUpdateService',
                        'customer_id'    => $customer->id,
                        'reference_type' => 'both_found',
                        'reference_date' => $referenceDate,
                    ],
                    200,
                    'Both payment and pay-later found, using most recent as reference date',
                    $traceId
                );
            } elseif ($lastPayment) {
                $referenceDate = $lastPayment->payment_time;

                LogHelper::logInfo(
                    null,
                    [
                        'service'        => 'CustomerUpdateService',
                        'customer_id'    => $customer->id,
                        'reference_type' => 'payment_only',
                        'reference_date' => $referenceDate,
                    ],
                    200,
                    'Using last payment as reference date',
                    $traceId
                );
            } elseif ($lastPayLater) {
                $referenceDate = $lastPayLater->pay_later;

                LogHelper::logInfo(
                    null,
                    [
                        'service'        => 'CustomerUpdateService',
                        'customer_id'    => $customer->id,
                        'reference_type' => 'paylater_only',
                        'reference_date' => $referenceDate,
                    ],
                    200,
                    'Using last pay-later as reference date',
                    $traceId
                );
            } else {
                $formatted = $activationDate->format('d_m_Y');

                LogHelper::logInfo(
                    null,
                    [
                        'service'        => 'CustomerUpdateService',
                        'customer_id'    => $customer->id,
                        'reference_type' => 'none_found',
                        'default_return' => $formatted,
                    ],
                    200,
                    'No payment or pay-later record found, using activation date',
                    $traceId
                );

                return $formatted;
            }

            // === Calculate next billing period ===
            $referenceDate = Carbon::parse($referenceDate);

            if ($customer->billing_period) {
                $nextMonth = Carbon::createFromFormat('F Y', $customer->billing_period)
                    ->addMonth()
                    ->format('m.Y');
            } else {
                $nextMonth = $referenceDate->addMonth()->format('m.Y');
            }

            $final = sprintf('%02d_%s', $day, $nextMonth);

            LogHelper::logInfo(
                null,
                [
                    'service'           => 'CustomerUpdateService',
                    'customer_id'       => $customer->id,
                    'final_billing_key' => $final,
                    'day'               => $day,
                    'next_month'        => $nextMonth,
                ],
                200,
                'Calculated last bill payment successfully',
                $traceId
            );

            return $final;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'service'       => 'CustomerUpdateService',
                    'exception'     => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ],
                    'customer_id'   => $customer->id ?? null,
                    'activation_date' => $activationDate ?? null,
                ],
                500,
                'Failed to calculate last bill payment',
                $traceId
            );

            throw $e;
        }
    }

    private function updateCustomerData(Customer $customer, array $data, string $traceId): void
    {
        try {
            LogHelper::logInfo(
                null,
                [
                    'service'            => 'CustomerUpdateService',
                    'customer_id'        => $customer->id,
                    'incoming_data_keys' => array_keys($data),
                ],
                200,
                'Starting customer data update process',
                $traceId
            );

            $formatter = new FormatReceiver();

            // === Build update payload ===
            $updateData = [
                'name'     => $data['name'] ?? $customer->name,
                'email'    => $data['email'] ?? $customer->email,
                'address'  => $data['address'] ?? $customer->address,
                'contact'  => $formatter->formatReceiver($data['contact'] ?? $customer->contact, null, false),
                'discount' => $data['discount'] ?? $customer->discount ?? 0,
                'user_id'  => $data['user_id'] ?? $customer->user_id,
            ];

            // Password handling
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
                LogHelper::logInfo(
                    null,
                    [
                        'service'     => 'CustomerUpdateService',
                        'customer_id' => $customer->id,
                        'note'        => 'Password field will be updated (hashed).',
                    ],
                    200,
                    'Customer password update detected',
                    $traceId
                );
            }

            // Member handling
            if (!empty($data['member_id'])) {
                $updateData['member_id']      = $data['member_id'];
                $updateData['billing_option'] = $data['billing_option'] ?? null;
                $updateData['billing_period'] = ($data['billing_option'] ?? '0') !== '0'
                    ? $data['periode_billing'] ?? null
                    : null;

                LogHelper::logInfo(
                    null,
                    [
                        'service'        => 'CustomerUpdateService',
                        'customer_id'    => $customer->id,
                        'member_id'      => $data['member_id'],
                        'billing_option' => $data['billing_option'] ?? null,
                        'billing_period' => $updateData['billing_period'],
                    ],
                    200,
                    'Member and billing info updated',
                    $traceId
                );
            }

            // === Perform update ===
            $customer->update($updateData);

            LogHelper::logInfo(
                null,
                [
                    'service'        => 'CustomerUpdateService',
                    'customer_id'    => $customer->id,
                    'updated_fields' => array_keys($updateData),
                    'updated_payload' => $updateData,
                ],
                200,
                'Customer data updated successfully',
                $traceId
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'service'     => 'CustomerUpdateService',
                    'exception'   => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ],
                    'customer_id' => $customer->id ?? null,
                    'data'        => $data,
                ],
                500,
                'Failed to update customer data',
                $traceId
            );

            throw $e;
        }
    }

    private function handleMemberChange(Customer $customer, array $data, string $traceId): void
    {
        try {
            // === EARLY EXIT CONDITIONS ===
            if (!$customer->wasChanged('member_id')) {
                LogHelper::logInfo(
                    null,
                    [
                        'service'     => 'CustomerUpdateService',
                        'customer_id' => $customer->id,
                        'note'        => 'Member ID not changed, skipping process.',
                    ],
                    200,
                    'Skipped member change (no change detected)',
                    $traceId
                );
                return;
            }

            if ($customer->billing_period === ($data['periode_billing'] ?? null)) {
                LogHelper::logInfo(
                    null,
                    [
                        'service'     => 'CustomerUpdateService',
                        'customer_id' => $customer->id,
                        'periode'     => $customer->billing_period,
                        'note'        => 'Customer billing periode same as request data.',
                    ],
                    200,
                    'Skipped member change (same billing periode)',
                    $traceId
                );
                return;
            }

            // Ambil billing periode aktif saat ini
            $currentPeriode = BillingPeriode::latest('created_at')->first();
            if (!$currentPeriode) {
                throw new \RuntimeException('No billing periode found');
            }

            if ($currentPeriode->name === ($data['periode_billing'] ?? null)) {
                LogHelper::logInfo(
                    null,
                    [
                        'service'     => 'CustomerUpdateService',
                        'customer_id' => $customer->id,
                        'periode'     => $currentPeriode->name,
                        'note'        => 'Current periode matches provided periode, skipping update.',
                    ],
                    200,
                    'Skipped member change (periode identical to current)',
                    $traceId
                );
                return;
            }

            // === LOAD RELASI ===
            $customer->load(['package', 'member']);
            if (!$customer->package) {
                throw new \RuntimeException("Customer package not found");
            }

            // === GET DURATION DAN KONFIGURASI ===
            $subscriptionDuration = SubscriptionDuration::where('package_id', $customer->package_id)
                ->where('is_active', true)
                ->first();

            $websystem = Websystem::where('slug', 'websystem')->firstOrFail();

            // === DISCOUNT & TAX CALCULATION ===
            $customerDiscount = $this->billingService->calculateCustomerDiscount(
                $customer,
                $currentPeriode->name
            );

            $discountPackage = $this->billingService->calculatePackageDiscount(
                $subscriptionDuration,
                $customer->package->price
            );

            $discountAmount      = (int) ($customerDiscount + $discountPackage);
            $priceAfterDiscount  = max(0, (int) $customer->package->price - $discountAmount);
            $taxRate             = (float) ($websystem->tax_rate ?? 0);
            $customerTax         = ($websystem->enable_tax)
                ? (int) ceil($priceAfterDiscount * ($taxRate / 100))
                : 0;
            $totalAmount         = $priceAfterDiscount + $customerTax;

            LogHelper::logInfo(
                null,
                [
                    'service'          => 'CustomerUpdateService',
                    'customer_id'      => $customer->id,
                    'periode'          => $currentPeriode->name,
                    'package_price'    => $customer->package->price,
                    'discount_customer' => $customerDiscount,
                    'discount_package' => $discountPackage,
                    'discount_total'   => $discountAmount,
                    'tax_amount'       => $customerTax,
                    'total_amount'     => $totalAmount,
                ],
                200,
                'Calculated new billing values before update',
                $traceId
            );

            // === UPDATE BILLING ===
            $updated = Billing::where('customer_id', $customer->id)
                ->whereIn('status', ['BL', 'PL'])
                ->whereHas('billingSubscription', function ($q) use ($currentPeriode) {
                    $q->where('billing_periode_id', $currentPeriode->id);
                })
                ->update([
                    'base_price'      => (int) $customer->package->price,
                    'discount_amount' => $discountAmount,
                    'subtotal'        => $priceAfterDiscount,
                    'total_amount'    => $totalAmount,
                    'tax_amount'      => $customerTax,
                ]);

            if ($updated > 0) {
                LogHelper::logInfo(
                    null,
                    [
                        'service'     => 'CustomerUpdateService',
                        'customer_id' => $customer->id,
                        'periode'     => $currentPeriode->name,
                        'updated'     => $updated,
                    ],
                    200,
                    'Billing updated successfully after member change',
                    $traceId
                );
            } else {
                LogHelper::logWarning(
                    null,
                    [
                        'service'     => 'CustomerUpdateService',
                        'customer_id' => $customer->id,
                        'periode'     => $currentPeriode->name,
                    ],
                    404,
                    'No billing record found to update after member change',
                    $traceId
                );
            }
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'service'     => 'CustomerUpdateService',
                    'exception'   => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ],
                    'customer_id' => $customer->id ?? null,
                    'periode'     => $data['periode_billing'] ?? null,
                ],
                500,
                'Failed to handle member change',
                $traceId
            );

            throw $e;
        }
    }

    private function switchCustomerSubscription(Customer $customer, int $newPackageId, string $traceId): array
    {
        $startTime = microtime(true);

        $log = function (string $level, int $status, string $message, array $context = []) use ($traceId, $customer, $startTime) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2) . ' ms';
            $context = array_merge([
                'service'        => 'CustomerUpdateService',
                'customer_id'    => $customer->id,
                'customer_name'  => $customer->name,
                'execution_time' => $executionTime
            ], $context);

            match ($level) {
                'info'    => LogHelper::logInfo(null, $context, $status, $message, $traceId),
                'warning' => LogHelper::logWarning(null, $context, $status, $message, $traceId),
                'error'   => LogHelper::logError(null, $context, $status, $message, $traceId),
            };
        };

        try {
            $result = DB::transaction(function () use ($customer, $newPackageId, $log, $traceId) {
                // Ambil customer subscription aktif
                $activeSubscription = CustomerSubscription::where('customer_id', $customer->id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first();

                if (!$activeSubscription) {
                    $log('warning', 404, 'Active customer subscription not found');
                    return [
                        'success' => false,
                        'message' => 'Active subscription not found',
                    ];
                }

                if ((int)$activeSubscription->package_id === (int)$newPackageId) {
                    $log('warning', 400, 'New package is the same as the old package');
                    return [
                        'success' => false,
                        'message' => 'New package is the same as the old package',
                    ];
                }

                // Terminate subscription lama
                $activeSubscription->update([
                    'status'        => 'terminated',
                    'terminated_at' => now(),
                ]);

                $log('info', 200, 'Old subscription terminated', [
                    'old_subscription_id' => $activeSubscription->id,
                ]);

                // Update billing_subscription_periodes lama -> is_expired = true, status = 'expired'
                BillingSubscriptionPeriode::where('customer_subscription_id', $activeSubscription->id)
                    ->update([
                        'is_expired' => true,
                        'status'     => 'expired',
                    ]);

                $log('info', 200, 'Old billing subscription periods marked expired', [
                    'old_subscription_id' => $activeSubscription->id,
                ]);

                $package = Package::find($newPackageId);
                if (!$package) {
                    $log('warning', 404, 'package not found');
                    return [
                        'success' => false,
                        'message' => 'package not found',
                    ];
                }

                $priceFinal = $package->is_instalation_free
                    ? $package->price
                    : $package->price + $package->installation_fee;

                $subscriptionDuration = SubscriptionDuration::where('package_id', $newPackageId)->first();

                // Buat customer_subscriptions baru
                $newSubscription = CustomerSubscription::create([
                    'customer_id'              => $customer->id,
                    'package_id'               => $newPackageId,
                    'subscription_duration_id' => $subscriptionDuration?->id,
                    'start_date'               => now(),
                    'end_date'                 => now()->addMonth()->day(15)->startOfDay(),
                    'price_final'              => $priceFinal,
                    'discount_type'            => 'none',
                    'discount_value_final'     => 0,
                    'status'                   => 'active',
                ]);

                $log('info', 201, 'New subscription created', [
                    'new_subscription_id' => $newSubscription->id,
                    'new_package_id'      => $newPackageId,
                ]);

                return [
                    'success'             => true,
                    'message'             => 'switch package done',
                    'new_subscription_id' => $newSubscription->id,
                ];
            });

            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => $result['message'],
                    'traceId' => $traceId
                ];
            }

            return [
                'success'             => true,
                'message'             => $result['message'],
                'new_subscription_id' => $result['new_subscription_id'],
                'traceId'             => $traceId
            ];
        } catch (\Throwable $e) {
            $log('error', 500, 'Failed to switch subscription', [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ],
            ]);

            return [
                'success' => false,
                'message' => 'switch package failed',
                'traceId' => $traceId
            ];
        }
    }
}
