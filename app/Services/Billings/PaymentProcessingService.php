<?php

namespace App\Services\Billings;

use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Models\Billing\BillingPeriode;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Jobs\AddPaymentAfterDueDateJob;
use App\Jobs\AddPaymentIpStaticAfterDueDateJob;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Http\Resources\Pppoe\SecretResource;
use Carbon\Carbon;
use Exception;

class PaymentProcessingService
{
    private SecretService $secretService;
    private IpStaticService $ipStaticService;

    public function __construct(SecretService $secretService, IpStaticService $ipStaticService)
    {
        $this->secretService = $secretService;
        $this->ipStaticService = $ipStaticService;
    }

    public function processPaymentActions(Customer $customer, string $traceId)
    {
        $router = Mikrotik::find($customer->mikrotik_id);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

        if (!$router || $router->disabled == 'true' || !$autoIsolir || $autoIsolir->disabled == 'true') {
            return;
        }

        $commentPayment = $this->getCommentPayment($autoIsolir, $customer);

        if ($customer->internet_service == 'ppp') {
            $this->processPppPayment($router, $customer, $commentPayment, $traceId);
        } elseif ($customer->internet_service == 'ip_static') {
            $this->processIpStaticPayment($router, $customer, $autoIsolir, $commentPayment, $traceId);
        }
    }

    private function getCommentPayment(AutoIsolir $autoIsolir, Customer $customer): string
    {
        if ($autoIsolir->activation_date == 'false') {
            return $autoIsolir->comment_payment;
        }

        $activationDate = Carbon::parse($customer->activation_date)->format('d');
        $periodeName = BillingPeriode::latest('created_at')->value('name');
        $nextMonth = Carbon::parse($periodeName)->addMonth()->format('m_Y');
        $totalDaysInMonth = Carbon::now()->addMonth()->daysInMonth;

        if ($activationDate > $totalDaysInMonth) {
            $activationDate = $totalDaysInMonth;
        }

        return $autoIsolir->comment_unpayment . '_' . $activationDate . '_' . $nextMonth;
    }

    private function processPppPayment(Mikrotik $router, Customer $customer, string $commentPayment, string $traceId)
    {
        try {
            $userSecretId = $this->secretService->getSecretById($router, $customer->secret_id, $traceId);

            if ($userSecretId == 'error') {
                throw new Exception($customer->secret_id . '! Cannot find user "' . $customer->username . '" on server ' . $router->name . '.');
            }

            $secretProfiles = SecretResource::collection($userSecretId)->value('profile');
            $customerProfile = $customer->paket->profile;

            if ($secretProfiles == $customerProfile) {
                dispatch(new AddPaymentBeforeDueDateJob($router, $commentPayment, $customer->secret_id, $traceId))
                    ->onQueue('default');
            } else {
                dispatch(new AddPaymentAfterDueDateJob(
                    $router,
                    $customerProfile,
                    $commentPayment,
                    $customer->secret_id,
                    $customer->username,
                    $traceId
                ))->onQueue('default');
            }
        } catch (Exception $e) {
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
        try {
            $ipAddress = $customer->ip_static;
            $simplequeId = $customer->simpleque_id;
            $addressListIsolir = $autoIsolir->address_list_isolir;
            $comment = $customer->username . '_' . $commentPayment;

            $addressListCustomer = $this->ipStaticService->getAddressListByIpAddress($router, $ipAddress, $traceId);

            if (isset($addressListCustomer[0]['list']) && $addressListCustomer[0]['list'] == $addressListIsolir) {
                dispatch(new AddPaymentIpStaticAfterDueDateJob(
                    $router,
                    $ipAddress,
                    $simplequeId,
                    $addressListIsolir,
                    $commentPayment,
                    $comment
                ))->onQueue('default');
            } else {
                dispatch(new AddPaymentBeforeDueDateJob($router, $simplequeId, $commentPayment, $traceId))
                    ->onQueue('default');
            }
        } catch (Exception $e) {
            throw new Exception('Cannot connect to Server ' . $router->name . '. ' . $e->getMessage());
        }
    }
}
