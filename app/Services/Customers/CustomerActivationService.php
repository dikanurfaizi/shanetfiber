<?php

namespace App\Services\Customers;

use App\Enums\WhatsAppAction;
use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\Paket\Paket;
use App\Models\AutoIsolir;
use App\Models\Billing\Billing;
use App\Models\Receipt;
use App\Models\PppType;
use App\Jobs\WhatsApp\ProcessWhatsappMessageJob;
use App\Jobs\Telegram\ProcessTelegramMessageJob;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\QueueService;
use App\Services\Routerboard\IpStaticService;
use App\DataTransferObjects\WhatsappJobDto;
use App\DataTransferObjects\TelegramJobDto;
use App\Helpers\LogHelper;

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

        try {
            foreach ($customers as $customer) {
                $this->activateSingleCustomerWithNotifications($customer, now()->format('d F Y'), $traceId);
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

    private function activateSingleCustomerWithNotifications(Customer $customer, string $activationDate, string $traceId): void
    {
        try {
            $customer->update([
                'activation_date' => $activationDate,
                'activation' => 'true',
                'disabled' => 'false',
            ]);

            $this->dispatchNotificationJobs($customer, $traceId);
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
        $activationDate = Carbon::now();
        $internet_service = $customer->internet_service;
        $mikrotikId = $customer->mikrotik_id;

        try {
            $router = Mikrotik::findOrFail($mikrotikId);
            if ($router->disabled == 'true') {
                $error = 'Server ' . $router->name . ' disable, please enable first.';
                LogHelper::logError(null, ['server' => $router->name], 400, $error, $traceId);
                return ['error' => $error];
            }

            $profile = Paket::where('id', $customer->paket_id)->value('profile');
            $comment_payment = $this->getCommentPayment($mikrotikId);

            if ($internet_service == 'ppp') {
                $result = $this->activatePppCustomer($customer, $router, $profile, $comment_payment, $activationDate, $traceId);
                if (isset($result['error'])) {
                    return $result;
                }
            } else if ($internet_service == 'ip_static') {
                $result = $this->activateIpStaticCustomer($customer, $router, $profile, $comment_payment, $activationDate, $traceId);
                if (isset($result['error'])) {
                    return $result;
                }
            }

            $this->createReceiptAndUpdateBilling($customer, $traceId, $activationDate);
            $this->dispatchNotificationJobs($customer, $traceId);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id
            ], 200, 'Customer activated successfully', $traceId);

            return ['success' => 'Customer activated.'];
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'customer_id' => $customer->id
            ], 500, 'Failed to activate customer', $traceId);
            return ['error' => 'Failed to activate customer: ' . $e->getMessage()];
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
                'activation_date' => $activationDate,
                'activation' => 'true',
                'disabled' => 'false',
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
                'activation_date' => $activationDate,
                'activation' => 'true',
                'disabled' => 'false',
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

    private function createReceiptAndUpdateBilling(Customer $customer, string $traceId, Carbon $activationDate): void
    {
        try {
            $billing = Billing::where('customer_id', $customer->id)->firstOrFail();

            $numberReceipt = $this->generateReceiptNumber();

            $receipt = Receipt::create([
                'slug' => str('inv-' . $customer->id . '-' . Str::random(4))->slug(),
                'periode' => $billing->billingSubscription->billing_periode,
                'number' => str(sprintf("%05d", $numberReceipt) . '-KWT-' . Carbon::now()->format('F') . '-' . Carbon::now()->format('Y')),
                'billing_number' => $billing->billing_number,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_address' => $customer->address,
                'paket_name' => $customer->paket->name,
                'paket_price' => $customer->paket->price * ((100 - $customer->discount) / 100),
                'payment_methode' => 'cash',
                'created_name' => auth()->user()->name,
                'teller_name' => auth()->user()->name,
                'payment_time' => $activationDate,
            ]);

            $billing->update([
                'payment_time' => $activationDate,
                'teller_name' => auth()->user()->name,
                'status' => 'LS',
                'receipt_id' => $receipt->id,
            ]);

            LogHelper::logInfo(null, [
                'receipt_id' => $receipt->id,
                'billing_id' => $billing->id
            ], 200, 'Receipt created and billing updated', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'customer_id' => $customer->id
            ], 500, 'Failed to create receipt and update billing', $traceId);
            throw $e;
        }
    }

    private function generateReceiptNumber(): int
    {
        try {
            $getLastReceipt = Receipt::latest('created_at')->value('number');
            return $getLastReceipt ? (int)explode('-', $getLastReceipt)[1] + 1 : 1;
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500, 'Failed to generate receipt number', 'system');
            return 1;
        }
    }

    private function dispatchNotificationJobs(Customer $customer, string $traceId): void
    {
        try {
            $whatsappJobData = new WhatsappJobDto(
                customerId: $customer->id,
                action: WhatsappAction::ACTIVATION->value,
                traceId: $traceId,
                billingId: null,
                receiptId: null,
                receiptData: null,
                startDate: null,
                endDate: null,
                orderId: null,
                oldPaketName: null,
                oldPaketPrice: null
            );

            $telegramJobData = new TelegramJobDto(
                customerId: $customer->id,
                action: 'activation_customer',
                traceId: $traceId,
                billingId: null,
                receiptId: null
            );

            ProcessWhatsappMessageJob::dispatch($whatsappJobData)->onQueue('whatsapp_single');
            ProcessTelegramMessageJob::dispatch($telegramJobData)->onQueue('telegram');

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id
            ], 200, 'Notification jobs dispatched', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'customer_id' => $customer->id
            ], 500, 'Failed to dispatch notification jobs', $traceId);
            throw $e;
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

            $profile = Paket::where('id', $customer->paket_id)->value('profile');
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
