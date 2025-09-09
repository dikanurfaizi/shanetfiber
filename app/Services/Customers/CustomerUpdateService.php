<?php

namespace App\Services\Customers;

use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Models\Paket\Paket;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\CustomerSubscription;
use App\Models\Billing\BillingSubscriptionPeriode;
use App\Models\SubscriptionDuration;
use App\Models\Websystem;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Jobs\AddPaymentIpStaticBeforeDueDateJob;
use App\Jobs\WhatsApp\ProcessWhatsappMessageJob;
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
use App\DataTransferObjects\WhatsappJobDto;
use App\Enums\WhatsAppAction;
use Illuminate\Support\Facades\DB;

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
            if ($action === 'change_package') {
                $result = $this->validatePackageChange($customer, $traceId);
                if (!$result['success']) {
                    return $result;
                }
                $changesResult = $this->handlePackageChange($customer, $validatedData, $traceId);
                if (!$changesResult['success']) {
                    return $changesResult;
                }
                $message = $changesResult['message'];
            } else {
                $this->handleActivationDateChange($customer, $validatedData, $traceId);
                $this->updateCustomerData($customer, $validatedData, $traceId);
                $this->handleMemberChange($customer, $traceId);
                $message = 'Customer update successfully';
            }

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'changes' => array_keys($validatedData)
                ],
                200,
                $message,
                $traceId
            );

            return [
                'success' => true,
                'message' => $message,
                'traceId' => $traceId
            ];
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'data' => $validatedData
                ],
                500,
                'Failed to update customer',
                $traceId
            );
            return [
                'success' => false,
                'message' => "Failed to update customer. Please call the IT Helpdesk",
                'traceId' => $traceId
            ];
        }
    }

    private function validatePackageChange(Customer $customer, string $traceId): array
    {
        $startTime = microtime(true);

        $log = function (string $level, int $status, string $message, array $context = []) use ($traceId, $customer, $startTime) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2) . ' ms';
            $context = array_merge([
                'customer_id' => $customer->id,
                'execution_time' => $executionTime
            ], $context);

            match ($level) {
                'info' => LogHelper::logInfo(null, $context, $status, $message, $traceId),
                'warning' => LogHelper::logWarning(null, $context, $status, $message, $traceId),
                'error' => LogHelper::logError(null, $context, $status, $message, $traceId),
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
                $log('warning', 404, 'Tidak ada data billing periode', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name
                ]);
                return [
                    'success' => false,
                    'message' => 'Package change failed, there is no billing periode',
                    'traceId' => $traceId
                ];
            }

            // Cek apakah periode terakhir sudah dibayar
            if (!$lastPeriode->is_paid) {
                $log('warning', 400, 'Periode terakhir belum dibayar', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
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

            if ($paidCount < 3) {
                $log('warning', 400, 'Belum berlangganan minimal 3 periode', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'paid_count' => $paidCount
                ]);
                return [
                    'success' => false,
                    'message' => 'Package change failed, have not subscribed for at least 3 periods',
                    'traceId' => $traceId
                ];
            }

            $log('info', 200, 'Validasi billing terpenuhi, lanjut proses ganti paket', [
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
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
            return [
                'success' => false,
                'message' => 'Unexpected error during change paket',
                'traceId' => $traceId
            ];
        }
    }

    private function handlePackageChange(Customer $customer, array $data, string $traceId): array
    {
        $startTime = microtime(true);

        $log = function (string $level, int $status, string $message, array $context = []) use ($traceId, $customer, $startTime) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2) . ' ms';
            $context = array_merge([
                'customer_id' => $customer->id,
                'execution_time' => $executionTime
            ], $context);

            match ($level) {
                'info' => LogHelper::logInfo(null, $context, $status, $message, $traceId),
                'warning' => LogHelper::logWarning(null, $context, $status, $message, $traceId),
                'error' => LogHelper::logError(null, $context, $status, $message, $traceId),
            };
        };

        try {
            if (!isset($data['paket_id'])) {
                $message = 'Package ID not present in request data';
                $log('info', 200, $message, [
                    'message' => 'Data paket_id tidak dikirim dari form'
                ]);
                return [
                    'success' => false,
                    'message' => $message,
                    'traceId' => $traceId
                ];
            }

            $log('info', 200, 'Checking for package change', [
                'paket_id_from_request' => $data['paket_id'],
                'paket_id_from_customer' => $customer->paket_id
            ]);

            if ($data['paket_id'] == $customer->paket_id) {
                $message = 'No package change detected';
                $log('info', 200, $message, [
                    'paket_id' => $data['paket_id']
                ]);
                return [
                    'success' => false,
                    'message' => $message,
                    'traceId' => $traceId
                ];
            }

            // Load router & auto isolir
            $customer->loadMissing(['mikrotik', 'autoIsolir', 'paket']);

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

            if (!$customer->autoIsolir) {
                $log('warning', 404, 'AutoIsolir tidak ditemukan', [
                    'mikrotik_id' => $customer->mikrotik_id
                ]);
            } else {
                $log('info', 200, 'AutoIsolir ditemukan', [
                    'mikrotik_id' => $customer->mikrotik_id,
                    'autoIsolir' => $customer->autoIsolir
                ]);
            }

            // Ambil Paket dan Profile
            $paket = Paket::find($data['paket_id']);
            $profile = $paket?->profile;

            if (!$paket || !$profile) {
                $message = 'Profile or Package not found';
                $log('warning', 404, $message, [
                    'paket_id' => $data['paket_id']
                ]);
                return [
                    'success' => false,
                    'message' => $message,
                    'traceId' => $traceId
                ];
            }

            $log('info', 200, 'Profile ditemukan dari paket', [
                'paket_id' => $data['paket_id'],
                'profile' => $profile
            ]);

            if ($customer->mikrotik && $customer->mikrotik->disabled == 'true') {
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

            try {
                $this->testConnectionService->checkOnline($customer->mikrotik, $traceId);
            } catch (\Exception $e) {
                $message = 'Router offline during package change';
                $log('error', 503, 'Router offline during package change', [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'router_id' => $customer->mikrotik->id
                ]);
                return [
                    'success' => false,
                    'message' => $message,
                    'traceId' => $traceId
                ];
            }

            // Update Mikrotik profile
            if ($customer->internet_service === 'ppp') {
                $this->updatePppPackage($customer, $customer->mikrotik, $customer->autoIsolir, $profile, $traceId);
                $log('info', 200, 'Send update Ppp Package', [
                    'paket_id' => $data['paket_id'],
                    'profile' => $profile
                ]);
            } elseif ($customer->internet_service === 'ip_static') {
                $this->updateIpStaticPackage($customer, $customer->mikrotik, $profile, $traceId);
                $log('info', 200, 'Send update IP Static Package', [
                    'paket_id' => $data['paket_id'],
                    'profile' => $profile
                ]);
            }

            $oldPaket = $customer->paket;

            $switchResult = $this->switchCustomerSubscription($customer, $paket->id, $traceId);
            if ($switchResult['success']) {
                $customer->update([
                    'paket_id' => $paket->id
                ]);

                $log('info', 200, 'Customer paket_id berhasil diperbarui', [
                    'new_paket_id' => $paket->id
                ]);

                $whatsappJobData = new WhatsappJobDto(
                    customerId: $customer->id,
                    action: WhatsappAction::CHANGES_PACKAGE->value,
                    traceId: $traceId,
                    billingId: null,
                    receiptId: null,
                    receiptData: null,
                    startDate: null,
                    endDate: null,
                    orderId: null,
                    oldPaketName: $oldPaket->name,
                    oldPaketPrice: $oldPaket->price
                );

                ProcessWhatsappMessageJob::dispatch($whatsappJobData)->onQueue('whatsapp_single');

                $successMessage = $switchResult['message'];
                return [
                    'success' => true,
                    'message' => 'Change and ' . $successMessage,
                    'traceId' => $traceId
                ];
            } else {
                $errorMessage = $switchResult['message'];
                return [
                    'success' => false,
                    'message' => 'Change and ' . $errorMessage,
                    'traceId' => $traceId
                ];
            }
        } catch (\Throwable $e) {
            $message = 'Unexpected error during paket changes';
            $log('error', 500, 'Unexpected error during handlePackageChange', [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
            return [
                'success' => false,
                'message' => $message,
                'traceId' => $traceId
            ];
        }
    }

    private function updatePppPackage(
        Customer $customer,
        Mikrotik $router,
        ?AutoIsolir $autoIsolir,
        string $profile,
        string $traceId
    ): void {
        try {
            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'secret_id'   => $customer->secret_id,
                    'router_id'   => $router->id,
                ],
                200,
                'Start updating PPP package',
                $traceId
            );
        
            // Step 1: Ambil secret
            $userSecret = $this->secretService->getSecretById($router, $customer->secret_id, $traceId);
            LogHelper::logInfo(
                null,
                ['result' => $userSecret],
                200,
                'Fetched secret by ID',
                $traceId
            );
        
            if ($userSecret === 'error') {
                throw new \RuntimeException("Cannot find user {$customer->username} on server");
            }
        
            // Step 2: Ambil profile dari secret
            $secretProfiles = SecretResource::collection($userSecret)->value('profile');
            LogHelper::logInfo(
                null,
                ['secret_profiles' => $secretProfiles],
                200,
                'Fetched current secret profile',
                $traceId
            );
        
            // Step 3: Update profile jika berbeda
            if (!$autoIsolir || $autoIsolir->profile_id != $secretProfiles) {
                LogHelper::logInfo(
                    null,
                    [
                        'current_profile' => $secretProfiles,
                        'new_profile'     => $profile
                    ],
                    200,
                    'Profile difference detected, updating profile',
                    $traceId
                );
            
                $response = $this->secretService->updateProfileSecret(
                    $router,
                    $profile,
                    $customer->secret_id,
                    $traceId
                );
            
                LogHelper::logInfo(
                    null,
                    ['update_response' => $response],
                    200,
                    'Profile updated',
                    $traceId
                );
            
                if (isset($response['after']['message'])) {
                    throw new \RuntimeException($response['after']['message']);
                }
            
                // Step 4: Hapus active secret
                $this->secretService->deleteActiveSecret($router, $customer->username, $traceId);
                LogHelper::logInfo(
                    null,
                    ['username' => $customer->username],
                    200,
                    'Deleted active secret',
                    $traceId
                );
            } else {
                LogHelper::logInfo(
                    null,
                    [
                        'current_profile' => $secretProfiles,
                        'new_profile'     => $profile
                    ],
                    200,
                    'Profile already matches, no update required',
                    $traceId
                );
            }
        
            // Step 5: Sukses
            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'new_profile' => $profile
                ],
                200,
                'PPP package updated successfully',
                $traceId
            );
        
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
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
                'Failed to update PPP package',
                $traceId
            );
            throw $e;
        }
    }

    private function updateIpStaticPackage(Customer $customer, Mikrotik $router, string $profile, string $traceId): void
    {
        try {
            $ipAddress = $this->ipStaticService->getIpByArpId($router, $customer->arp_id, $traceId);

            if ($ipAddress === 'error') {
                throw new \RuntimeException("Cannot find ARP entry for customer");
            }

            $profileData = $this->secretService->getPppProfileByName($router, $profile, $traceId)[0];
            $rateLimit = $profileData['rate-limit'] ?? '';

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
                    'customer_id' => $customer->id,
                    'new_profile' => $profile,
                    'rate_limit' => $rateLimit
                ],
                200,
                'IP Static package updated successfully',
                $traceId
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'router_id' => $router->id
                ],
                500,
                'Failed to update IP Static package',
                $traceId
            );
            throw $e;
        }
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
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'data' => $data,
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

        $router = Mikrotik::find($customer->mikrotik_id);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

        if (!$router || $router->disabled == 'true' || !$autoIsolir || $autoIsolir->disabled == 'true') {
            LogHelper::logWarning(
                null,
                [
                    'customer_id' => $customer->id,
                    'router_status' => $router ? ($router->disabled ? 'disabled' : 'enabled') : 'not_found',
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

            if ($autoIsolir->activation_date == 'true') {
                $comment = $autoIsolir->comment_unpayment . '_' . $lastBillPayment;

                $success = false;

                if ($customer->internet_service == 'ppp') {
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
                            'customer_id' => $customer->id,
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
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
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
        try {
            dispatch(new AddPaymentBeforeDueDateJob($router, $comment, $secretId, $traceId));
            return true;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'router_id' => $router->id,
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
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'router_id' => $router->id,
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
            $activationDate = Carbon::parse($activationDate);
            $totalDaysInMonth = $activationDate->daysInMonth;
            $day = min($activationDate->day, $totalDaysInMonth);

            $lastPayment = Billing::where('customer_id', $customer->id)
                ->where('status', 'LS')
                ->latest('payment_time')
                ->first();

            $lastPayLater = Billing::where('customer_id', $customer->id)
                ->where('status', 'PL')
                ->latest('pay_later')
                ->first();

            if ($lastPayLater && $lastPayment) {
                $referenceDate = $lastPayLater->pay_later > $lastPayment->payment_time
                    ? $lastPayLater->pay_later
                    : $lastPayment->payment_time;
            } elseif ($lastPayment) {
                $referenceDate = $lastPayment->payment_time;
            } elseif ($lastPayLater) {
                $referenceDate = $lastPayLater->pay_later;
            } else {
                return $activationDate->format('d_m_Y');
            }

            $referenceDate = Carbon::parse($referenceDate);

            if ($customer->billing_period) {
                $nextMonth = Carbon::createFromFormat('F Y', $customer->billing_period)
                    ->addMonth()
                    ->format('m.Y');
                return sprintf('%02d_%s', $day, $nextMonth);
            }

            $nextMonth = $referenceDate->addMonth()->format('m.Y');
            return sprintf('%02d_%s', $day, $nextMonth);
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'activation_date' => $activationDate
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
            $formatter = new FormatReceiver();
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'address' => $data['address'],
                'contact' => $formatter->formatReceiver($data['contact'], null, false),
                'discount' => $data['discount'] ?? 0,
                'user_id' => $data['user_id'],
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            if (!empty($data['member_id'])) {
                $updateData['member_id'] = $data['member_id'];
                $updateData['billing_option'] = $data['billing_option'] ?? null;
                $updateData['billing_period'] = $data['billing_option'] !== '0' ? $data['periode_billing'] : null;
            }

            $customer->update($updateData);

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'updated_fields' => array_keys($updateData)
                ],
                200,
                'Customer data updated successfully',
                $traceId
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'data' => $data
                ],
                500,
                'Failed to update customer data',
                $traceId
            );
            throw $e;
        }
    }

    private function handleMemberChange(Customer $customer, string $traceId): void
    {
        if (!$customer->wasChanged('member_id')) {
            return;
        }

        try {
            $customer->load(['paket', 'member']);

            $currentPeriode = BillingPeriode::latest('created_at')->first();
            if (!$currentPeriode) {
                throw new \RuntimeException('No billing periode found');
            }

            $subscriptionDuration = SubscriptionDuration::where('customer_id', $customer->id)
                ->where('paket_id', $customer->paket_id)
                ->where('status', 'active')
                ->first();

            if (!$customer->paket) {
                throw new \RuntimeException("Customer paket not found");
            }

            $websystem = Websystem::where('slug', 'websystem')->firstOrFail();

            // Pastikan casting aman
            $customerDiscount = $this->billingService->calculateCustomerDiscount(
                $customer,
                $currentPeriode->name
            );

            $discountPaket = $this->billingService->calculatePaketDiscount(
                $subscriptionDuration,
                $customer->paket->price
            );

            $discountAmount = (int) ($customerDiscount + $discountPaket);
            $priceAfterDiscount = max(0, (int) $customer->paket->price - $discountAmount);

            $taxRate = (float) ($websystem->tax_rate ?? 0);
            $customerTax = ($websystem->enable_tax)
                ? (int) ceil($priceAfterDiscount * ($taxRate / 100))
                : 0;

            $totalAmount = $priceAfterDiscount + $customerTax;

            $updated = Billing::where('customer_id', $customer->id)
                ->whereIn('status', ['BL', 'PL'])
                ->whereHas('billingSubscription', function ($q) use ($currentPeriode) {
                    $q->where('billing_periode_id', $currentPeriode->id);
                })
                ->update([
                    'base_price'      => (int) $customer->paket->price,
                    'discount_amount' => $discountAmount,
                    'subtotal'        => $priceAfterDiscount,
                    'total_amount'    => $totalAmount,
                    'tax_amount'      => $customerTax,
                ]);


            if ($updated > 0) {
                LogHelper::logInfo(
                    null,
                    [
                        'customer_id'     => $customer->id,
                        'base_price'      => $customer->paket->price,
                        'discount_amount' => $discountAmount,
                        'subtotal'        => $priceAfterDiscount,
                        'total_amount'    => $totalAmount,
                        'tax_amount'      => $customerTax,
                        'periode'         => $currentPeriode->name,
                    ],
                    200,
                    'Billing price updated after member change',
                    $traceId
                );
            } else {
                LogHelper::logWarning(
                    null,
                    [
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
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ],
                    'customer_id' => $customer->id,
                ],
                500,
                'Failed to handle member change',
                $traceId
            );
            throw $e;
        }
    }

    private function switchCustomerSubscription(Customer $customer, int $newPaketId, string $traceId): array
    {
        DB::beginTransaction();
        $startTime = microtime(true);

        $log = function (string $level, int $status, string $message, array $context = []) use ($traceId, $customer, $startTime) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2) . ' ms';
            $context = array_merge([
                'customer_id' => $customer->id,
                'execution_time' => $executionTime
            ], $context);

            match ($level) {
                'info' => LogHelper::logInfo(null, $context, $status, $message, $traceId),
                'warning' => LogHelper::logWarning(null, $context, $status, $message, $traceId),
                'error' => LogHelper::logError(null, $context, $status, $message, $traceId),
            };
        };

        try {
            // Ambil customer subscription aktif
            $activeSubscription = CustomerSubscription::where('customer_id', $customer->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$activeSubscription) {
                $log('warning', 404, 'Active customer subscription not found');
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Active subscription not found',
                    'traceId' => $traceId
                ];
            }

            if ($activeSubscription->paket_id == $newPaketId) {
                $log('warning', 400, 'New package is the same as the old package');
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'New package is the same as the old package',
                    'traceId' => $traceId
                ];
            }

            // Terminate subscription lama
            $activeSubscription->update([
                'status' => 'terminated',
                'terminated_at' => now(),
            ]);

            $log('info', 200, 'Old subscription terminated', [
                'old_subscription_id' => $activeSubscription->id,
            ]);

            // Update billing_subscription_periodes lama -> is_expired = true, status = 'expired'
            BillingSubscriptionPeriode::where('customer_subscription_id', $activeSubscription->id)
                ->update([
                    'is_expired' => true,
                    'status' => 'expired',
                ]);

            $log('info', 200, 'Old billing subscription periods marked expired', [
                'old_subscription_id' => $activeSubscription->id,
            ]);

            $paket = Paket::find($newPaketId);
            if (!$paket) {
                $log('warning', 404, 'Paket not found');
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Paket not found',
                    'traceId' => $traceId
                ];
            }

            $priceFinal = $paket->is_instalation_free ? $paket->price : $paket->price + $paket->installation_fee;

            $subscriptionDuration = SubscriptionDuration::where('paket_id', $newPaketId)->first();

            // Buat customer_subscriptions baru
            $newSubscription = CustomerSubscription::create([
                'customer_id' => $customer->id,
                'paket_id' => $newPaketId,
                'subscription_duration_id' => $subscriptionDuration->id,
                'start_date' => now(),
                'end_date' => now()->addMonth()->day(15)->startOfDay(),
                'price_final' => $priceFinal,
                'discount_type' => 'none',
                'discount_value_final' => 0,
                'status' => 'active',
            ]);

            $log('info', 201, 'New subscription created', [
                'new_subscription_id' => $newSubscription->id,
                'new_paket_id' => $newPaketId,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'switch paket done',
                'new_subscription_id' => $newSubscription->id,
                'traceId' => $traceId
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            $log('error', 500, 'Failed to switch subscription', [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ]);

            return [
                'success' => false,
                'message' => 'switch paket failed',
                'traceId' => $traceId
            ];
        }
    }
}
