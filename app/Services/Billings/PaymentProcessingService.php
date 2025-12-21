<?php

namespace App\Services\Billings;

use App\Helpers\ExecutionTimer;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Models\Billings\BillingPeriode;
use App\Models\Billings\Billing;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Jobs\AddPaymentAfterDueDateJob;
use App\Jobs\AddPaymentIpStaticAfterDueDateJob;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Http\Resources\Pppoe\SecretResource;
use Carbon\Carbon;
use Exception;
use App\Helpers\LogHelper;
use App\Models\Customers\CustomerSubscription;

class PaymentProcessingService
{
    private SecretService $secretService;
    private IpStaticService $ipStaticService;

    public function __construct(SecretService $secretService, IpStaticService $ipStaticService)
    {
        $this->secretService = $secretService;
        $this->ipStaticService = $ipStaticService;
    }

    public function processPaymentActions(Billing $billing, string $traceId)
    {
        $timer = new ExecutionTimer();
        try {
            $billing->loadMissing(['customer']);
            $customer = $billing->customer;

            if (!$customer) {
                LogHelper::logError(null, ['billing_id' => $billing->id], "Customer not found for billing", $traceId, $timer->lap());
                return;
            }

            $router = Mikrotik::find($customer->mikrotik_id);
            $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            if (!$router || $router->disabled === 'true') {
                LogHelper::logError(null, ['router_id' => $customer->mikrotik_id], "Router not found or disabled", $traceId, $timer->lap());
                return;
            }
            if (!$autoIsolir || $autoIsolir->disabled === 'true') {
                LogHelper::logError(null, ['mikrotik_id' => $customer->mikrotik_id], "AutoIsolir not found or disabled", $traceId, $timer->lap());
                return;
            }

            $commentPayment = $this->getCommentPayment($autoIsolir, $billing, $traceId);

            if ($customer->internet_service == 'ppp') {
                $this->processPppPayment($router, $customer, $commentPayment, $traceId);
            } elseif ($customer->internet_service == 'ip_static') {
                $this->processIpStaticPayment($router, $customer, $autoIsolir, $commentPayment, $traceId);
            } else {
                LogHelper::logWarning(null, ['customer_id' => $customer->id, 'internet_service' => $customer->internet_service], "Unknown internet service type", $traceId, $timer->lap());
            }
        } catch (\Exception $e) {
            LogHelper::logError(null, ['billing_id' => $billing->id, 'trace' => $e->getTraceAsString()], "processPaymentActions exception: " . $e->getMessage(), $traceId, $timer->lap());
            throw $e;
        }
    }

    private function getCommentPayment(AutoIsolir $autoIsolir, Billing $billing, string $traceId): string
    {
        $timer = new ExecutionTimer();
        try {
            if ($autoIsolir->activation_date === 'false') {
                return $autoIsolir->comment_payment;
            }

            $billing->loadMissing('billingSubscriptionPeriode.billingPeriode'); // asumsi relasi sudah dibuat di model

            $activationDate = Carbon::parse($billing->customer->activation_date)->format('d');

            $billingSubPeriode = $billing->billingSubscriptionPeriode;
            $billingPeriode = $billingSubPeriode ? $billingSubPeriode->billingPeriode : null;

            if (!$billingSubPeriode || !$billingPeriode) {
                LogHelper::logWarning(null, ['billing_id' => $billing->id], "Billing subscription periode or periode not found", $traceId, $timer->lap());
                return $autoIsolir->comment_payment; // fallback
            }

            $nextMonth = Carbon::parse($billingPeriode->name)->addMonth()->format('m_Y');
            $totalDaysInMonth = Carbon::parse($billingPeriode->name)->copy()->addMonth()->daysInMonth;

            if ((int)$activationDate > $totalDaysInMonth) {
                $activationDate = $totalDaysInMonth;
            }

            return $autoIsolir->comment_unpayment . '_' . $activationDate . '_' . $nextMonth;
        } catch (\Exception $e) {
            LogHelper::logError(null, ['billing_id' => $billing->id], "getCommentPayment exception: " . $e->getMessage(), $traceId, $timer->lap());
            return $autoIsolir->comment_payment; // fallback on error
        }
    }

    private function processPppPayment(Mikrotik $router, Customer $customer, string $commentPayment, string $traceId)
    {
        $timer = new ExecutionTimer();
        try {
            $userSecretId = $this->secretService->getSecretById($router, $customer->secret_id, $traceId);

            if ($userSecretId === 'error') {
                throw new Exception($customer->secret_id . '! Cannot find user "' . $customer->username . '" on server ' . $router->name . '.');
            }

            $secretProfiles = SecretResource::collection($userSecretId)->value('profile');
            $customerSubscription = CustomerSubscription::with(['package'])
                ->where('customer_id', $customer->id)
                ->where('status', 'active')->first();
            $customerProfile = $customerSubscription->package->profile ?? null;

            $context = [
                'customer_id' => $customer->name,
                'customer_profile' => $customerProfile,
                'internet_service' => $customer->internet_service,
                'comment_payment' => $commentPayment
            ];

            if ($secretProfiles === $customerProfile) {
                if (config('services.shanetfiber.allow_mikrotik_actions')) {
                    dispatch(new AddPaymentBeforeDueDateJob($router, $commentPayment, $customer->secret_id, $traceId))
                        ->onQueue('default');
                }
                $context['job_name'] = 'AddPaymentBeforeDueDateJob';
            } else {
                if (config('services.shanetfiber.allow_mikrotik_actions')) {
                    dispatch(new AddPaymentAfterDueDateJob(
                        $router,
                        $customerProfile,
                        $commentPayment,
                        $customer->secret_id,
                        $customer->username,
                        $traceId
                    ))->onQueue('default');
                }
                $context['job_name'] = 'AddPaymentAfterDueDateJob';
            }
            LogHelper::logInfo(null, $context, 200, "Process PPP payment patched successfully", $traceId, $timer->lap());
        } catch (Exception $e) {
            LogHelper::logError(null, ['router' => $router->name, 'customer_id' => $customer->id], "processPppPayment exception: " . $e->getMessage(), $traceId, $timer->lap());
            throw new Exception('Server ' . $router->name . ' offline, please check your server. ' . $e->getMessage());
        }
    }

    private function processIpStaticPayment(
        Mikrotik $router,
        Customer $customer,
        AutoIsolir $autoIsolir,
        string $commentPayment,
        string $traceId
    ) {
        $timer = new ExecutionTimer();
        try {
            $ipAddress = $customer->ip_static;
            $simplequeId = $customer->simpleque_id;
            $addressListIsolir = $autoIsolir->address_list_isolir;
            $comment = $customer->username . '_' . $commentPayment;

            $addressListCustomer = $this->ipStaticService->getAddressListByIpAddress($router, $ipAddress, $traceId);

            $context = [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'ip_address' => $ipAddress,
                'simple_queue_id' => $simplequeId,
                'address_list_isolir' => $addressListIsolir,
                'address_list_customer' => $addressListCustomer,
                'internet_service' => $customer->internet_service,
                'comment_payment' => $comment
            ];

            if (isset($addressListCustomer[0]['list']) && $addressListCustomer[0]['list'] == $addressListIsolir) {
                dispatch(new AddPaymentIpStaticAfterDueDateJob(
                    $router,
                    $ipAddress,
                    $simplequeId,
                    $addressListIsolir,
                    $commentPayment,
                    $comment,
                    $traceId
                ))->onQueue('default');
                $context['job_name'] = 'AddPaymentIpStaticAfterDueDateJob';
            } else {
                dispatch(new AddPaymentBeforeDueDateJob($router, $simplequeId, $commentPayment, $traceId))
                    ->onQueue('default');
                $context['job_name'] = 'AddPaymentBeforeDueDateJob';
            }
            LogHelper::logInfo(null, $context, 200, "Successfully dispatch job", $traceId, $timer->lap());
        } catch (Exception $e) {
            LogHelper::logError(null, ['router' => $router->name, 'customer_id' => $customer->id], "processIpStaticPayment exception: " . $e->getMessage(), $traceId, $timer->lap());
            throw new Exception('Cannot connect to Server ' . $router->name . '. ' . $e->getMessage());
        }
    }
}
