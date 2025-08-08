<?php

namespace App\Services\Customers;

use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Models\Paket\Paket;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\Paket\PppProfile;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Jobs\AddPaymentIpStaticBeforeDueDateJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\QueueService;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\TestConnectionService;
use App\Http\Resources\Pppoe\SecretResource;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;

class CustomerUpdateService
{
    public function __construct(
        private SecretService $secretService,
        private QueueService $queueService,
        private IpStaticService $ipStaticService,
        private TestConnectionService $testConnectionService
    ) {}

    public function updateCustomer(Customer $customer, array $validatedData, string $traceId, string $action): void
    {
        try {
            if ($action === 'change_package') {
                $this->handlePackageChange($customer, $validatedData, $traceId);
            }
            $this->handleActivationDateChange($customer, $validatedData, $traceId);
            $this->updateCustomerData($customer, $validatedData, $traceId);
            $this->handleMemberChange($customer, $traceId);

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'changes' => array_keys($validatedData)
                ],
                200,
                'Customer updated successfully',
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
                    'data' => $validatedData
                ],
                500,
                'Failed to update customer',
                $traceId
            );
            throw $e;
        }
    }

    private function handlePackageChange(Customer $customer, array $data, string $traceId): void
    {
        if (!isset($data['paket_id'])) {
            return;
        }

        if ($data['paket_id'] == $customer->paket_id) {
            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'paket_id' => $data['paket_id']
                ],
                200,
                'No package change detected',
                $traceId
            );
            return;
        }

        $router = Mikrotik::find($customer->mikrotik_id);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();
        $profile = Paket::find($data['paket_id'])->value('profile');

        if (!$router || $router->disabled == 'true') {
            LogHelper::logWarning(
                null,
                [
                    'customer_id' => $customer->id,
                    'router_status' => $router ? 'disabled' : 'not_found'
                ],
                400,
                'Router not available for package change',
                $traceId
            );
            return;
        }

        try {
            $this->testConnectionService->checkOnline($router, $traceId);
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
                    'customer_id' => $customer->id,
                    'router_id' => $router->id
                ],
                503,
                'Router offline during package change',
                $traceId
            );
            return;
        }

        if ($customer->internet_service === 'ppp') {
            $this->updatePppPackage($customer, $router, $autoIsolir, $profile, $traceId);
        } elseif ($customer->internet_service === 'ip_static') {
            $this->updateIpStaticPackage($customer, $router, $profile, $traceId);
        }
    }

    private function updatePppPackage(Customer $customer, Mikrotik $router, ?AutoIsolir $autoIsolir, string $profile, string $traceId): void
    {
        try {
            $userSecret = $this->secretService->getSecretById($router, $customer->secret_id, $traceId);

            if ($userSecret === 'error') {
                throw new \RuntimeException("Cannot find user {$customer->username} on server");
            }

            $secretProfiles = SecretResource::collection($userSecret)->value('profile');

            if (!$autoIsolir || $autoIsolir->profile_id != $secretProfiles) {
                $response = $this->secretService->updateProfileSecret($router, $profile, $customer->secret_id, $traceId);

                if (isset($response['after']['message'])) {
                    throw new \RuntimeException($response['after']['message']);
                }

                $this->secretService->deleteActiveSecret($router, $customer->username, $traceId);
            }

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

            if (!empty($data['paket_id'])) {
                $profile = PppProfile::find($data['paket_id']);
                $paket = Paket::where('profile', $profile->profile_name)->first();
                $updateData['paket_id'] = $paket->id;
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
            $newPrice = max(0, $customer->paket->price - ($customer->member->discount ?? 0));

            $currentPeriode = BillingPeriode::latest('created_at')->first();
            $shouldUpdate = $this->shouldUpdateBilling($customer, $currentPeriode, $traceId);

            if ($shouldUpdate) {
                $updated = Billing::where('customer_id', $customer->id)
                    ->where('billing_periode_id', $currentPeriode->id)
                    ->whereIn('status', ['BL', 'PL'])
                    ->update(['paket_price' => $newPrice]);

                if ($updated) {
                    LogHelper::logInfo(
                        null,
                        [
                            'customer_id' => $customer->id,
                            'new_price' => $newPrice,
                            'periode' => $currentPeriode->name
                        ],
                        200,
                        'Billing price updated after member change',
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
                'Failed to handle member change',
                $traceId
            );
            throw $e;
        }
    }

    private function shouldUpdateBilling(Customer $customer, BillingPeriode $periode, string $traceId): bool
    {
        return ($customer->billing_option == 1) ||
            ($customer->billing_option == 2 && $customer->billing_period == $periode->name);
    }
}
