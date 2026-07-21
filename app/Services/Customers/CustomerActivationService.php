<?php

namespace App\Services\Customers;

use App\Helpers\ExecutionTimer;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Models\Packages\Package;
use App\Models\AutoIsolir;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPayment;
use App\Models\PppType;
use App\Models\Customers\CustomerSubscription;
use Carbon\Carbon;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\QueueService;
use App\Services\Routerboard\IpStaticService;
use App\Services\Receipts\CreateReceiptService;
use App\Services\Receipts\ReferenceIdGeneratorService;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;

class CustomerActivationService
{
    public function __construct(
        private SecretService $secretService,
        private QueueService $queueService,
        private IpStaticService $ipStaticService,
        private CreateReceiptService $createReceiptService,
        private ReferenceIdGeneratorService $referenceIdGeneratorService,
    ) {}

    public function activateAllCustomers(int $mikrotikId, string $traceId): array
    {
        $timer = new ExecutionTimer();
        $server = Mikrotik::where('id', $mikrotikId)->value('name');

        if (!$server) {
            $error = "Server Mikrotik tidak ditemukan dengan ID {$mikrotikId}.";
            LogHelper::logError(null, ['mikrotik_id' => $mikrotikId], 404, $error, $traceId, $timer->lap());

            return [
                'error'    => $error,
                'trace_id' => $traceId,
            ];
        }

        $customers = Customer::where('mikrotik_id', $mikrotikId)->get();

        if ($customers->isEmpty()) {
            $error = "Tidak ada customer yang terhubung ke server {$server}.";
            LogHelper::logError(null, ['server' => $server], 404, $error, $traceId, $timer->lap());

            return [
                'error'    => $error,
                'trace_id' => $traceId,
            ];
        }

        $activationDate = Carbon::now();

        try {
            foreach ($customers as $customer) {
                $this->microtikActivate($customer, $customer->activeSubscription, $activationDate, $traceId);
            }

            LogHelper::logInfo(null, [
                'count'  => count($customers),
                'server' => $server,
            ], 200, 'Successfully activated all customers', $traceId, $timer->lap());

            return [
                'success'  => 'Activated ' . count($customers) . ' customers on server ' . $server . '.',
                'count'    => count($customers),
                'server'   => $server,
                'trace_id' => $traceId,
            ];
        } catch (\Throwable $e) {
            $this->logException($e, 'Failed to activate all customers', ['mikrotik_id' => $mikrotikId], $traceId, $timer);

            return [
                'error'    => "Terjadi kesalahan saat aktivasi: " . $e->getMessage(),
                'trace_id' => $traceId,
            ];
        }
    }

    private function microtikActivate(Customer $customer, CustomerSubscription $subs, Carbon $activationDate, string $traceId): array
    {
        $timer = new ExecutionTimer();

        try {
            $router = Mikrotik::findOrFail($customer->mikrotik_id);

            if ($router->disabled === 'true') {
                $error = "Server {$router->name} disabled, please enable first.";
                LogHelper::logError(null, ['server' => $router->name], 400, $error, $traceId, $timer->lap());

                return ['error' => $error];
            }

            $profile = $subs->package->profile;
            $commentPayment = $this->getCommentPayment($router->id);

            $result = match ($customer->internet_service) {
                'ppp'       => $this->activatePppCustomer($customer, $router, $profile, $commentPayment, $activationDate, $traceId),
                'ip_static' => $this->activateIpStaticCustomer($customer, $router, $profile, $commentPayment, $activationDate, $traceId),
                default     => ['error' => "Unsupported service type: {$customer->internet_service}"],
            };

            if (isset($result['error'])) {
                LogHelper::logError(null, [
                    'cust_id'   => $customer->id,
                    'cust_name' => $customer->name,
                    'server'    => $router->name,
                    'service'   => $customer->internet_service,
                ], 500, $result['error'], $traceId, $timer->lap());
            }

            return ['result' => $result];
        } catch (\Throwable $e) {
            $this->logException($e, 'Failed to activate single customer', ['customer_id' => $customer->id], $traceId, $timer);
            throw $e;
        }
    }

    public function activateCustomer(Customer $customer, string $traceId): array
    {
        $timer = new ExecutionTimer();
        $activationDate = now();
        $paymentMethod = 'cash';

        try {
            $allResult = DB::transaction(function () use ($customer, $activationDate, $traceId, $paymentMethod, $timer) {
                /** -----------------------------------------
                 * 1. Ambil subscription aktif customer
                 * ----------------------------------------- */
                $subscription = CustomerSubscription::query()
                    ->with(['customer', 'package'])
                    ->where('customer_id', $customer->id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->firstOrFail();

                /** -----------------------------------------
                 * 2. Ambil billing terkait subscription
                 * ----------------------------------------- */
                $billing = Billing::query()
                    ->with(['billingSubscription.billingPeriode'])
                    ->whereHas(
                        'billingSubscription',
                        fn($q) => $q->where('customer_subscription_id', $subscription->id)
                    )
                    ->lockForUpdate()
                    ->latest()
                    ->firstOrFail();

                /** -----------------------------------------
                 * 3. Update billing (PAID)
                 * ----------------------------------------- */
                $billing->update([
                    'status'     => 'LS',
                    'updated_by' => auth()->id(),
                ]);

                /** -----------------------------------------
                 * 4. Insert billing payment
                 * ----------------------------------------- */
                $billingPayment = BillingPayment::create([
                    'billing_id'      => $billing->id,
                    'amount_paid'     => $billing->total_amount,
                    'payment_method'  => $paymentMethod,
                    'reference_id'    => $this->referenceIdGeneratorService->generate('ACT'),
                    'paid_by_user_id' => auth()->id(),
                    'payment_time'    => $activationDate,
                ]);

                /** -----------------------------------------
                 * 5. Buat receipt
                 * ----------------------------------------- */
                $result = $this->createReceiptService
                    ->createReceipt(
                        $subscription,
                        $billing,
                        $billingPayment,
                        ['payment_method' => $paymentMethod],
                        $traceId
                    );

                /** -----------------------------------------
                 * 6. Update customer
                 * ----------------------------------------- */
                $customer->update([
                    'activation_date' => $activationDate,
                    'activation'      => 'true',
                    'disabled'        => 'false',
                ]);

                $customer->refresh();
                $billing->refresh();

                LogHelper::logInfo(
                    null,
                    [
                        'customerId'       => $customer->id,
                        'customerName'     => $customer->name,
                        'billingId'        => $billing->id,
                        'billingPaymentId' => $billingPayment->id,
                        'receiptId'        => $result['receipt']->id,
                    ],
                    200,
                    "Customer, Billing, BillingPayment, Receipt Has been updated",
                    $traceId,
                    $timer->lap()
                );

                return [
                    'customer'       => $customer,
                    'billing'        => $billing,
                    'billingPayment' => $billingPayment,
                    'receipt'        => $result['receipt'],
                    'subscription'   => $subscription,
                    'membership'     => $result['membership'],
                ];
            });

            /** -----------------------------------------
             * 7. SIDE EFFECT (di luar transaction)
             * ----------------------------------------- */
            if (config('services.shanetfiber.allow_mikrotik_actions')) {
                $this->microtikActivate($customer, $customer->activeSubscription, $activationDate, $traceId);
            }

            return [
                'success'        => true,
                'customer'       => $allResult['customer'],
                'billing'        => $allResult['billing'],
                'billingPayment' => $allResult['billingPayment'],
                'receipt'        => $allResult['receipt'],
                'traceId'        => $traceId,
                'subscription'   => $allResult['subscription'],
                'membership'     => $allResult['membership'],
            ];
        } catch (\Throwable $e) {
            $this->logException($e, 'Customer activation failed', ['customer_id' => $customer->id], $traceId, $timer);
            throw $e;
        }
    }

    private function getCommentPayment(int $mikrotikId): string
    {
        $timer = new ExecutionTimer();

        try {
            $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();

            if (!$autoIsolir) {
                return 'create_from_customer_management';
            }

            if ($autoIsolir->activation_date === 'true') {
                $totalDaysInNextMonth = Carbon::now()->addMonth()->daysInMonth;
                $day = Carbon::now()->format('d');

                if ($day > $totalDaysInNextMonth) {
                    $day = $totalDaysInNextMonth;
                }

                $nextMonth = Carbon::now()->addMonth()->format('m_Y');

                return $autoIsolir->comment_unpayment . '_' . $day . '_' . $nextMonth;
            }

            return $autoIsolir->comment_payment;
        } catch (\Throwable $e) {
            $this->logException($e, 'Failed to get comment payment', ['mikrotik_id' => $mikrotikId], 'system', $timer);

            return 'create_from_customer_management';
        }
    }

    private function activatePppCustomer(Customer $customer, Mikrotik $router, string $profile, string $commentPayment, Carbon $activationDate, string $traceId): array
    {
        $timer = new ExecutionTimer();
        $username = $customer->username;
        $password = $customer->password_ppp;
        $serviceName = PppType::where('id', $customer->ppp_type_id)->value('name');

        try {
            $createSecret = $this->secretService->createSecret($router, $username, $password, $serviceName, $profile, $commentPayment, $traceId);

            if (isset($createSecret['after']['message'])) {
                $error = "Cannot create PPP user on server {$router->name}.";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'server'      => $router->name,
                    'response'    => $createSecret,
                ], 400, $error, $traceId, $timer->lap());

                return ['error' => $error];
            }

            $secretId = $createSecret['after']['ret'];
            $customer->update(['secret_id' => $secretId]);

            $this->secretService->enableSecret($router, $username, $traceId);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'secret_id'   => $secretId,
                'server'      => $router->name,
            ], 200, 'PPPoE customer activated successfully', $traceId, $timer->lap());

            return [];
        } catch (\Throwable $e) {
            $this->logException($e, 'Failed to activate PPPoE customer', [
                'customer_id' => $customer->id,
                'server'      => $router->name,
            ], $traceId, $timer);

            return ['error' => "Cannot connect to the server {$router->name}."];
        }
    }

    private function activateIpStaticCustomer(Customer $customer, Mikrotik $router, string $profileName, string $commentPayment, Carbon $activationDate, string $traceId): array
    {
        $timer = new ExecutionTimer();

        try {
            $profileOnMikrotik = $this->secretService->getPppProfileByName($router, $profileName, $traceId);
            $profile = $profileOnMikrotik[0] ?? null;
            $valueLimitasi = $profile['rate-limit'] ?? '';

            if (empty($valueLimitasi)) {
                $error = "Please create limitasi PPP Profile {$profileName} on server {$router->name}!";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'profile'     => $profileName,
                    'server'      => $router->name,
                ], 400, $error, $traceId, $timer->lap());

                return ['error' => $error];
            }

            $parts = explode(' ', $valueLimitasi);
            $maxlimit = $parts[0] ?? '0/0';
            $priority = isset($parts[4]) ? "{$parts[4]}/{$parts[4]}" : '8/8';
            $limitat  = $parts[5] ?? '0/0';

            $ipStatic   = $customer->ip_static;
            $macAddress = $customer->mac_address;
            $interface  = $customer->interface;
            $parent     = $profile['parent-queue'] ?? 'none';
            $queueType  = $profile['queue-type'] ?? '';
            $comment    = $customer->username ?? 'Create from Customer Management';

            // 1. Create Simple Queue
            $createLimitasi = $this->queueService->createSimpleQueue(
                $router,
                $customer->username,
                $ipStatic,
                $parent,
                $priority,
                $queueType,
                $limitat,
                $maxlimit,
                $commentPayment,
                $traceId
            );

            if (isset($createLimitasi['after']['message'])) {
                $error = "Cannot create queue limit on server {$router->name}: {$createLimitasi['after']['message']}.";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'server'      => $router->name,
                    'response'    => $createLimitasi,
                ], 400, $error, $traceId, $timer->lap());

                return ['error' => $error];
            }

            $simpleQueueId = $createLimitasi['after']['ret'];

            // 2. Add ARP Entry
            $createArp = $this->ipStaticService->addIpToArp($router, $ipStatic, $macAddress, $interface, $comment, $traceId);

            if (isset($createArp['after']['message'])) {
                // Rollback Queue jika ARP gagal
                $this->ipStaticService->deleteIpFromSimpleQueue($router, $simpleQueueId, $traceId);

                $error = "Cannot create ARP entry for IP {$ipStatic} on server {$router->name}: {$createArp['after']['message']}.";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'ip_static'   => $ipStatic,
                    'server'      => $router->name,
                    'response'    => $createArp,
                ], 400, $error, $traceId, $timer->lap());

                return ['error' => $error];
            }

            $arpId = $createArp['after']['ret'];

            // 3. Update local DB
            $customer->update([
                'simpleque_id' => $simpleQueueId,
                'arp_id'       => $arpId,
            ]);

            LogHelper::logInfo(null, [
                'customer_id'  => $customer->id,
                'simpleque_id' => $simpleQueueId,
                'arp_id'       => $arpId,
                'server'       => $router->name,
            ], 200, 'IP Static customer activated successfully', $traceId, $timer->lap());

            return [];
        } catch (\Throwable $e) {
            $this->logException($e, 'Failed to activate IP Static customer', [
                'customer_id' => $customer->id,
                'server'      => $router->name,
            ], $traceId, $timer);

            return ['error' => "Cannot connect to the server {$router->name}."];
        }
    }

    public function exportCustomerSecret(Customer $customer, string $traceId): array
    {
        $timer = new ExecutionTimer();
        $mikrotikId = $customer->mikrotik_id;

        try {
            $router = Mikrotik::find($mikrotikId);

            if (!$router || $router->disabled === 'true') {
                $error = 'Server ' . ($router->name ?? $mikrotikId) . ' is disabled or not found, please enable first.';
                LogHelper::logError(null, ['server' => $router->name ?? null], 400, $error, $traceId, $timer->lap());

                return ['error' => $error];
            }

            $profile = Package::where('id', $customer->activeSubscription->package_id)->value('profile');
            $commentPayment = $this->getCommentPayment($mikrotikId);

            return match ($customer->internet_service) {
                'ppp'       => $this->exportPppSecret($customer, $router, $profile, $commentPayment, $traceId),
                'ip_static' => $this->exportIpStaticSecret($customer, $router, $profile, $commentPayment, $traceId),
                default     => function () use ($customer, $traceId, $timer) {
                    $error = 'Unsupported internet service type';
                    LogHelper::logError(null, ['customer_id' => $customer->id], 400, $error, $traceId, $timer->lap());

                    return ['error' => $error];
                }
            };
        } catch (\Throwable $e) {
            $this->logException($e, 'Failed to export customer secret', ['customer_id' => $customer->id], $traceId, $timer);

            return ['error' => 'Failed to export customer secret: ' . $e->getMessage()];
        }
    }

    private function exportPppSecret(Customer $customer, Mikrotik $router, string $profile, string $commentPayment, string $traceId): array
    {
        $timer = new ExecutionTimer();
        $username = $customer->username;
        $password = $customer->password_ppp;
        $serviceName = PppType::where('id', $customer->ppp_type_id)->value('name');

        try {
            $createSecret = $this->secretService->createSecret($router, $username, $password, $serviceName, $profile, $commentPayment, $traceId);

            if (isset($createSecret['after']['message'])) {
                $error = "Cannot create PPP user on server {$router->name}.";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'server'      => $router->name,
                    'response'    => $createSecret,
                ], 400, $error, $traceId, $timer->lap());

                return ['error' => $error];
            }

            $secretId = $createSecret['after']['ret'];
            $customer->update([
                'activation' => 'true',
                'disabled'   => 'false',
                'secret_id'  => $secretId,
            ]);

            $this->secretService->enableSecret($router, $username, $traceId);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'secret_id'   => $secretId,
                'server'      => $router->name,
            ], 200, 'PPPoE secret exported', $traceId, $timer->lap());

            return ['success' => 'Successfully exported PPPoE secret for customer'];
        } catch (\Throwable $e) {
            $this->logException($e, 'Failed to export PPPoE secret', [
                'customer_id' => $customer->id,
                'server'      => $router->name,
            ], $traceId, $timer);

            return ['error' => "Cannot connect to the server {$router->name}."];
        }
    }

    private function exportIpStaticSecret(Customer $customer, Mikrotik $router, string $profileName, string $commentPayment, string $traceId): array
    {
        $timer = new ExecutionTimer();

        try {
            $profileOnMikrotik = $this->secretService->getPppProfileByName($router, $profileName, $traceId);
            $profile = $profileOnMikrotik[0] ?? null;
            $valueLimitasi = $profile['rate-limit'] ?? '';

            if (empty($valueLimitasi)) {
                $error = "Please create limitasi PPP Profile {$profileName} on server {$router->name}!";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'profile'     => $profileName,
                    'server'      => $router->name,
                ], 400, $error, $traceId, $timer->lap());

                return ['error' => $error];
            }

            $parts = explode(' ', $valueLimitasi);
            $maxlimit = $parts[0] ?? '0/0';
            $priority = isset($parts[4]) ? "{$parts[4]}/{$parts[4]}" : '8/8';
            $limitat  = $parts[5] ?? '0/0';

            $ipStatic   = $customer->ip_static;
            $macAddress = $customer->mac_address;
            $interface  = $customer->interface;
            $parent     = $profile['parent-queue'] ?? 'none';
            $queueType  = $profile['queue-type'] ?? '';
            $comment    = $customer->username ?? 'Create from Customer Management';

            $createLimitasi = $this->queueService->createSimpleQueue(
                $router,
                $customer->username,
                $ipStatic,
                $parent,
                $priority,
                $queueType,
                $limitat,
                $maxlimit,
                $commentPayment,
                $traceId
            );

            if (isset($createLimitasi['after']['message'])) {
                $error = "Cannot create queue limit on server {$router->name}, {$createLimitasi['after']['message']}.";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'server'      => $router->name,
                    'response'    => $createLimitasi,
                ], 400, $error, $traceId, $timer->lap());

                return ['error' => $error];
            }

            $simpleQueueId = $createLimitasi['after']['ret'];
            $createArp = $this->ipStaticService->addIpToArp($router, $ipStatic, $macAddress, $interface, $comment, $traceId);

            if (isset($createArp['after']['message'])) {
                $this->ipStaticService->deleteIpFromSimpleQueue($router, $simpleQueueId, $traceId);

                $error = "Cannot create ARP entry for IP {$ipStatic} on server {$router->name}, {$createArp['after']['message']}.";
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'ip_static'   => $ipStatic,
                    'server'      => $router->name,
                    'response'    => $createArp,
                ], 400, $error, $traceId, $timer->lap());

                return ['error' => $error];
            }

            $arpId = $createArp['after']['ret'];
            $customer->update([
                'activation'   => 'true',
                'disabled'     => 'false',
                'simpleque_id' => $simpleQueueId,
                'arp_id'       => $arpId,
            ]);

            LogHelper::logInfo(null, [
                'customer_id'  => $customer->id,
                'simpleque_id' => $simpleQueueId,
                'arp_id'       => $arpId,
                'server'       => $router->name,
            ], 200, 'IP Static configuration exported', $traceId, $timer->lap());

            return ['success' => 'Successfully exported IP Static configuration for customer'];
        } catch (\Throwable $e) {
            $this->logException($e, 'Failed to export IP Static configuration', [
                'customer_id' => $customer->id,
                'server'      => $router->name,
            ], $traceId, $timer);

            return ['error' => "Cannot connect to the server {$router->name}."];
        }
    }

    /**
     * Reusable helper method for exception logging with timer.
     */
    private function logException(\Throwable $e, string $message, array $context, string $traceId, ?ExecutionTimer $timer = null): void
    {
        $payload = array_merge([
            'exception' => [
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ],
        ], $context);

        LogHelper::logError(null, $payload, 500, $message, $traceId, $timer?->lap());
    }
}
