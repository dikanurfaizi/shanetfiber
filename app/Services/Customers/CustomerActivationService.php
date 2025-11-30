<?php

namespace App\Services\Customers;

use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\Packages\Package;
use App\Models\AutoIsolir;
use App\Models\Billing\Billing;
use App\Models\Receipt;
use App\Models\PppType;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\QueueService;
use App\Services\Routerboard\IpStaticService;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;

class CustomerActivationService
{
    public function __construct(
        private SecretService $secretService,
        private QueueService $queueService,
        private IpStaticService $ipStaticService
    ) {}

    public function activateAllCustomers(int $mikrotikId, string $traceId): array
    {
        $server = Mikrotik::where('id', $mikrotikId)->value('name');
        if (!$server) {
            $error = "Server Mikrotik tidak ditemukan dengan ID $mikrotikId.";
            LogHelper::logError(null, ['mikrotik_id' => $mikrotikId], 404, $error, $traceId);
            return [
                'error' => $error,
                'trace_id' => $traceId,
            ];
        }

        $customers = Customer::where('mikrotik_id', $mikrotikId)->get();
        if ($customers->isEmpty()) {
            $error = "Tidak ada customer yang terhubung ke server $server.";
            LogHelper::logError(null, ['server' => $server], 404, $error, $traceId);
            return [
                'error' => $error,
                'trace_id' => $traceId,
            ];
        }

        $activationDate = Carbon::now();

        try {
            foreach ($customers as $customer) {
                $result = $this->microtikActivate($customer, $activationDate, $traceId);
            }

            LogHelper::logInfo(null, [
                'count' => count($customers),
                'server' => $server
            ], 200, 'Successfully activated all customers', $traceId);

            return [
                'success' => 'Activated ' . count($customers) . ' customers on server ' . $server . '.',
                'count' => count($customers),
                'server' => $server,
                'trace_id' => $traceId,
            ];
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'mikrotik_id' => $mikrotikId
            ], 500, 'Failed to activate all customers', $traceId);

            return [
                'error' => "Terjadi kesalahan saat aktivasi: " . $e->getMessage(),
                'trace_id' => $traceId,
            ];
        }
    }

    private function microtikActivate(Customer $customer, Carbon $activationDate, string $traceId): array
    {
        $internet_service = $customer->internet_service;
        $mikrotikId = $customer->mikrotik_id;
        try {
            $router = Mikrotik::findOrFail($mikrotikId);
            if ($router->disabled == 'true') {
                $error = 'Server ' . $router->name . ' disable, please enable first.';
                LogHelper::logError(null, ['server' => $router->name], 400, $error, $traceId);
                $result = $error;
            }

            $profile = Package::where('id', $customer->package_id)->value('profile');
            $comment_payment = $this->getCommentPayment($mikrotikId);

            if ($internet_service == 'ppp') {
                $result = $this->activatePppCustomer($customer, $router, $profile, $comment_payment, $activationDate, $traceId);
                if (isset($result['error'])) {
                    LogHelper::logError(null, [
                        'cust_id' => $customer->id,
                        'cust_name' => $customer->name,
                        'server' => $router->name
                    ], 500, $result['error'], $traceId);
                }
            } else if ($internet_service == 'ip_static') {
                $result = $this->activateIpStaticCustomer($customer, $router, $profile, $comment_payment, $activationDate, $traceId);
                if (isset($result['error'])) {
                    LogHelper::logError(null, [
                        'cust_id' => $customer->id,
                        'cust_name' => $customer->name,
                        'server' => $router->name
                    ], 500, $result['error'], $traceId);
                }
            }

            // $this->dispatchNotificationJobs($customer, $traceId);

            return ['result' => $result];
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'customer_id' => $customer->id
            ], 500, 'Failed to activate single customer', $traceId);
            throw $e;
        }
    }

    public function activateCustomer(Customer $customer, string $traceId): array
    {
        $activationDate = now();

        try {
            $result = $this->createReceiptAndUpdateBilling($customer, $traceId, $activationDate);

            if (isset($result['status']) && $result['status'] === 'success') {
                if (config('services.shanetfiber.allow_mikrotik_actions')) {
                    $this->microtikActivate($customer, $activationDate, $traceId);
                }

                $customer->update([
                    'activation_date' => $activationDate,
                    'activation' => 'true',
                    'disabled' => 'false',
                ]);
            }

            $customer->refresh();

            LogHelper::logInfo('Activation', [
                'customer_id' => $customer->id,
                'billing_id' => $result['billing']->id,
                'receipt_id' => $result['receipt']->id,
            ], 200, 'Customer activated successfully', $traceId);

            return [
                'success' => true,
                'billing' => $result['billing'],
                'receipt' => $result['receipt'],
                'customer' => $customer,
                'traceId' => $traceId,
            ];
        } catch (\Throwable $e) {

            LogHelper::logError('Activation', [
                'customer_id' => $customer->id,
                'exception' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ], 500, 'Customer activation failed', $traceId);

            return [
                'error' => 'Activation failed: ' . $e->getMessage(),
                'customer_id' => $customer->id,
                'trace_id' => $traceId,
            ];
        }
    }


    private function getCommentPayment(int $mikrotikId): string
    {
        try {
            $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();

            if (!$autoIsolir) {
                return 'create_from_customer_management';
            }

            if ($autoIsolir->activation_date == 'true') {
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
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'mikrotik_id' => $mikrotikId
            ], 500, 'Failed to get comment payment', 'system');
            return 'create_from_customer_management';
        }
    }

    private function activatePppCustomer(Customer $customer, Mikrotik $router, string $profile, string $comment_payment, Carbon $activationDate, string $traceId): array
    {
        $username = $customer->username;
        $password = $customer->password_ppp;
        $serviceName = PppType::where('id', $customer->ppp_type_id)->value('name');

        try {
            $createSecret = $this->secretService->createSecret($router, $username, $password, $serviceName, $profile, $comment_payment, $traceId);

            if (isset($createSecret['after']['message'])) {
                $error = 'Cannot create PPP user on server ' . $router->name . '.';
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'server' => $router->name,
                    'response' => $createSecret
                ], 400, $error, $traceId);
                return ['error' => $error];
            }

            $secretId = $createSecret['after']['ret'];
            $customer->update([
                'secret_id' => $secretId,
            ]);

            $this->secretService->enableSecret($router, $username, $traceId);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'secret_id' => $secretId
            ], 200, 'PPPoE customer activated', $traceId);

            return [];
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'customer_id' => $customer->id,
                'server' => $router->name
            ], 500, 'Failed to activate PPPoE customer', $traceId);
            return ['error' => 'Cannot connect to the server ' . $router->name . '.'];
        }
    }

    private function activateIpStaticCustomer(Customer $customer, Mikrotik $router, string $profile, string $comment_payment, Carbon $activationDate, string $traceId): array
    {
        $username = $customer->username;
        $comment = $customer->username ?? 'Create from Customer Management';
        $profile_on_mikrotik = $this->secretService->getPppProfileByName($router, $profile, $traceId);
        $profile = $profile_on_mikrotik[0];
        $valueLimitasi = $profile['rate-limit'] ?? '';

        if (empty($valueLimitasi)) {
            $error = 'Please create limitasi PPP Profile ' . $profile['name'] . ' on server ' . $router->name . '!';
            LogHelper::logError(null, [
                'customer_id' => $customer->id,
                'profile' => $profile['name'],
                'server' => $router->name
            ], 400, $error, $traceId);
            return ['error' => $error];
        }

        $parts = explode(' ', $valueLimitasi);
        $ip_static = $customer->ip_static;
        $mac_address = $customer->mac_address;
        $interface = $customer->interface;
        $parent = $profile['parent-queue'] ?? 'none';
        $priority = $parts[4] . '/' . $parts[4];
        $queuetype = $profile['queue-type'] ?? '';
        $limitat = $parts[5];
        $maxlimit = $parts[0];

        try {
            $createLimitasi = $this->queueService->createSimpleQueue(
                $router,
                $username,
                $ip_static,
                $parent,
                $priority,
                $queuetype,
                $limitat,
                $maxlimit,
                $comment_payment,
                $traceId
            );

            if (isset($createLimitasi['after']['message'])) {
                $error = 'Cannot create queue limit on server ' . $router->name . ', ' . $createLimitasi['after']['message'] . '.';
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'server' => $router->name,
                    'response' => $createLimitasi
                ], 400, $error, $traceId);
                return ['error' => $error];
            }

            $simpleque_id = $createLimitasi['after']['ret'];
            $createArp = $this->ipStaticService->addIpToArp($router, $ip_static, $mac_address, $interface, $comment, $traceId);

            if (isset($createArp['after']['message'])) {
                $this->ipStaticService->deleteIpFromSimpleQueue($router, $simpleque_id, $traceId);
                $error = 'Cannot create ARP entry for IP ' . $ip_static . ' on server ' . $router->name . ', ' . $createArp['after']['message'] . '.';
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'ip_static' => $ip_static,
                    'server' => $router->name,
                    'response' => $createArp
                ], 400, $error, $traceId);
                return ['error' => $error];
            }

            $arp_id = $createArp['after']['ret'];
            $customer->update([
                'simpleque_id' => $simpleque_id,
                'arp_id' => $arp_id,
            ]);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'simpleque_id' => $simpleque_id,
                'arp_id' => $arp_id
            ], 200, 'IP Static customer activated', $traceId);

            return [];
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'customer_id' => $customer->id,
                'server' => $router->name
            ], 500, 'Failed to activate IP Static customer', $traceId);
            return ['error' => 'Cannot connect to the server ' . $router->name . '.'];
        }
    }

    private function createReceiptAndUpdateBilling(Customer $customer, string $traceId, Carbon $activationDate): array
    {
        LogHelper::logInfo('ActivationBilling', [
            'customer_id' => $customer->id,
            'activation_date' => $activationDate
        ], 200, 'Start createReceiptAndUpdateBilling', $traceId);

        DB::beginTransaction();

        try {
            // Fetch billing
            $billing = Billing::where('customer_id', $customer->id)->firstOrFail();

            // Generate receipt number
            $numberReceipt = $this->generateReceiptNumber($traceId);

            // Create receipt
            $receipt = Receipt::create([
                'slug' => str('inv-' . $customer->id . '-' . Str::random(4))->slug(),
                'periode' => $billing->billingSubscription->billing_periode,
                'number' => sprintf("%05d", $numberReceipt) . '-KWT-' . now()->format('F-Y'),
                'billing_number' => $billing->billing_number,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_address' => $customer->address,
                'package_name' => $customer->package->name,
                'package_price' => $customer->package->price * ((100 - $customer->discount) / 100),
                'payment_methode' => 'cash',
                'created_name' => auth()->user()->name ?? 'System',
                'teller_name' => auth()->user()->name ?? 'System',
                'payment_time' => $activationDate,
            ]);

            // Update billing
            $billing->update([
                'payment_time' => $activationDate,
                'teller_name' => auth()->user()->name ?? 'System',
                'status' => 'LS',
                'receipt_id' => $receipt->id,
            ]);

            // Update billingSubscription
            $billing->billingSubscription->update([
                'is_paid' => true,
                'status' => 'paid',
                'paid_at' => today()
            ]);

            DB::commit();

            $billing = $billing->fresh()->load('billingSubscription');
            $receipt = $receipt->fresh();

            LogHelper::logInfo('ActivationBilling', [
                'customer_id' => $customer->id,
                'billing_id' => $billing->id,
                'receipt_id' => $receipt->id,
            ], 200, 'createReceiptAndUpdateBilling success', $traceId);

            // === RETURN RESULT ===
            return [
                'status' => 'success',
                'billing' => $billing,
                'receipt' => $receipt,
                'billing_subscription' => $billing->billingSubscription
            ];
        } catch (\Throwable $e) {

            DB::rollBack();

            LogHelper::logError('ActivationBilling', [
                'customer_id' => $customer->id,
                'exception' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ], 500, 'createReceiptAndUpdateBilling failed', $traceId);

            throw $e; // <-- Tetap throw!
        }
    }

    private function generateReceiptNumber(string $traceId): string
    {
        try {
            $lastReceipt = Receipt::latest('created_at')->value('number');

            LogHelper::logInfo('GenerateReceiptNumber', [
                'trace_id' => $traceId,
                'last_receipt_number' => $lastReceipt
            ], 200, 'Generating new receipt number', $traceId);

            if ($lastReceipt && str_starts_with($lastReceipt, 'KWT-')) {
                $parts = explode('-', $lastReceipt);
                $number = (int)$parts[1] + 1;
            } else {
                $number = 1;
            }

            $newNumber = 'KWT-' . sprintf("%05d", $number) . '-' .
                Carbon::now()->format('F') . '-' . Carbon::now()->format('Y');

            LogHelper::logInfo('GenerateReceiptNumber', [
                'trace_id' => $traceId,
                'generated_receipt_number' => $newNumber
            ], 200, 'Receipt number generated successfully', $traceId);

            return $newNumber;
        } catch (\Throwable $e) {
            LogHelper::logError('GenerateReceiptNumber', [
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to generate receipt number', $traceId);

            // fallback kalau gagal
            return 'KWT-ERROR-' . Carbon::now()->timestamp;
        }
    }

    public function exportCustomerSecret(Customer $customer, string $traceId): array
    {
        $mikrotikId = $customer->mikrotik_id;

        try {
            $router = Mikrotik::find($mikrotikId);

            if ($router->disabled == 'true') {
                $error = 'Server ' . $router->name . ' disable, please enable first.';
                LogHelper::logError(null, ['server' => $router->name], 400, $error, $traceId);
                return ['error' => $error];
            }

            $profile = Package::where('id', $customer->package_id)->value('profile');
            $comment_payment = $this->getCommentPayment($mikrotikId);

            if ($customer->internet_service == 'ppp') {
                return $this->exportPppSecret($customer, $router, $profile, $comment_payment, $traceId);
            } else if ($customer->internet_service == 'ip_static') {
                return $this->exportIpStaticSecret($customer, $router, $profile, $comment_payment, $traceId);
            }

            $error = 'Unsupported internet service type';
            LogHelper::logError(null, ['customer_id' => $customer->id], 400, $error, $traceId);
            return ['error' => $error];
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'customer_id' => $customer->id
            ], 500, 'Failed to export customer secret', $traceId);
            return ['error' => 'Failed to export customer secret: ' . $e->getMessage()];
        }
    }

    private function exportPppSecret(Customer $customer, Mikrotik $router, string $profile, string $comment_payment, string $traceId): array
    {
        $username = $customer->username;
        $password = $customer->password_ppp;
        $serviceName = PppType::where('id', $customer->ppp_type_id)->value('name');

        try {
            $createSecret = $this->secretService->createSecret($router, $username, $password, $serviceName, $profile, $comment_payment, $traceId);

            if (isset($createSecret['after']['message'])) {
                $error = 'Cannot create PPP user on server ' . $router->name . '.';
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'server' => $router->name,
                    'response' => $createSecret
                ], 400, $error, $traceId);
                return ['error' => $error];
            }

            $secretId = $createSecret['after']['ret'];
            $customer->update([
                'activation' => 'true',
                'disabled' => 'false',
                'secret_id' => $secretId,
            ]);

            $this->secretService->enableSecret($router, $username, $traceId);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'secret_id' => $secretId
            ], 200, 'PPPoE secret exported', $traceId);

            return ['success' => 'Successfully exported PPPoE secret for customer'];
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'customer_id' => $customer->id,
                'server' => $router->name
            ], 500, 'Failed to export PPPoE secret', $traceId);
            return ['error' => 'Cannot connect to the server ' . $router->name . '.'];
        }
    }

    private function exportIpStaticSecret(Customer $customer, Mikrotik $router, string $profile, string $comment_payment, string $traceId): array
    {
        $username = $customer->username;
        $comment = $customer->username ?? 'Create from Customer Management';
        $profile_on_mikrotik = $this->secretService->getPppProfileByName($router, $profile, $traceId);
        $profile = $profile_on_mikrotik[0];
        $valueLimitasi = $profile['rate-limit'] ?? '';

        if (empty($valueLimitasi)) {
            $error = 'Please create limitasi PPP Profile ' . $profile['name'] . ' on server ' . $router->name . '!';
            LogHelper::logError(null, [
                'customer_id' => $customer->id,
                'profile' => $profile['name'],
                'server' => $router->name
            ], 400, $error, $traceId);
            return ['error' => $error];
        }

        $parts = explode(' ', $valueLimitasi);
        $ip_static = $customer->ip_static;
        $mac_address = $customer->mac_address;
        $interface = $customer->interface;
        $parent = $profile['parent-queue'] ?? 'none';
        $priority = $parts[4] . '/' . $parts[4];
        $queuetype = $profile['queue-type'] ?? '';
        $limitat = $parts[5];
        $maxlimit = $parts[0];

        try {
            $createLimitasi = $this->queueService->createSimpleQueue(
                $router,
                $username,
                $ip_static,
                $parent,
                $priority,
                $queuetype,
                $limitat,
                $maxlimit,
                $comment_payment,
                $traceId
            );

            if (isset($createLimitasi['after']['message'])) {
                $error = 'Cannot create queue limit on server ' . $router->name . ', ' . $createLimitasi['after']['message'] . '.';
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'server' => $router->name,
                    'response' => $createLimitasi
                ], 400, $error, $traceId);
                return ['error' => $error];
            }

            $simpleque_id = $createLimitasi['after']['ret'];
            $createArp = $this->ipStaticService->addIpToArp($router, $ip_static, $mac_address, $interface, $comment, $traceId);

            if (isset($createArp['after']['message'])) {
                $this->ipStaticService->deleteIpFromSimpleQueue($router, $simpleque_id, $traceId);
                $error = 'Cannot create ARP entry for IP ' . $ip_static . ' on server ' . $router->name . ', ' . $createArp['after']['message'] . '.';
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'ip_static' => $ip_static,
                    'server' => $router->name,
                    'response' => $createArp
                ], 400, $error, $traceId);
                return ['error' => $error];
            }

            $arp_id = $createArp['after']['ret'];
            $customer->update([
                'activation' => 'true',
                'disabled' => 'false',
                'simpleque_id' => $simpleque_id,
                'arp_id' => $arp_id,
            ]);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'simpleque_id' => $simpleque_id,
                'arp_id' => $arp_id
            ], 200, 'IP Static configuration exported', $traceId);

            return ['success' => 'Successfully exported IP Static configuration for customer'];
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'customer_id' => $customer->id,
                'server' => $router->name
            ], 500, 'Failed to export IP Static configuration', $traceId);
            return ['error' => 'Cannot connect to the server ' . $router->name . '.'];
        }
    }
}
