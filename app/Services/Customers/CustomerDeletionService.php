<?php

namespace App\Services\Customers;

use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Jobs\Secrets\DeletePppSecretJob;
use App\Jobs\Secrets\DeleteIpStaticJob;
use App\Services\Routerboard\TestConnectionService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\IpStaticService;
use App\Helpers\LogHelper;

class CustomerDeletionService
{
    protected $testConnectionService;
    protected $secretService;
    protected $ipStaticService;

    public function __construct(
        TestConnectionService $testConnectionService,
        SecretService $secretService,
        IpStaticService $ipStaticService
    ) {
        $this->testConnectionService = $testConnectionService;
        $this->secretService = $secretService;
        $this->ipStaticService = $ipStaticService;
    }

    public function deleteCustomer(Customer $customer, string $traceId)
    {
        LogHelper::logInfo(null, ['customer_id' => $customer->id], 200, "Start deleteCustomer", $traceId);

        if ($customer->activation !== 'true') {
            LogHelper::logInfo(null, ['customer_id' => $customer->id], 202, "Customer not active, proceed with soft delete", $traceId);
            return $this->softDeleteCustomer($customer, $traceId);
        }

        $router = Mikrotik::find($customer->mikrotik_id);
        $autoisolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

        try {
            LogHelper::logInfo(null, ['router_id' => $router->id], 200, "Checking router connection", $traceId);
            $this->testConnectionService->checkOnline($router, $traceId);
            LogHelper::logInfo(null, ['router_id' => $router->id], 200, "Router online", $traceId);
        } catch (\Exception $e) {
            LogHelper::logError($e, ['router_id' => $router->id], 503, "Router offline", $traceId);
            throw new \Exception('Router offline, please check your router.');
        }

        if ($customer->internet_service === 'ppp') {
            LogHelper::logInfo(null, ['username' => $customer->username], 200, "Deleting PPP service", $traceId);
            $this->deletePPP($customer, $router, $traceId);
        } elseif ($customer->internet_service === 'ip_static') {
            LogHelper::logInfo(null, ['ip' => $customer->ip_static], 200, "Deleting IP Static service", $traceId);
            $this->deleteIpStatic($customer, $router, $autoisolir, $traceId);
        }

        return $this->softDeleteCustomer($customer, $traceId);
    }

    protected function deletePPP(Customer $customer, $router, string $traceId)
    {
        $username = $customer->username;
        LogHelper::logInfo(null, ['router_id' => $router->id, 'username' => $username], 200, "Dispatch DeletePppSecretJob", $traceId);

        dispatch(new DeletePppSecretJob(
            $router,
            $username,
            $traceId
        ))->onQueue('default');
    }

    protected function deleteIpStatic(Customer $customer, $router, $autoisolir = null, string $traceId)
    {
        $simpleque_id = $customer->simpleque_id;
        $ip_address = $customer->ip_static;
        $arp_id = $customer->arp_id;
        $address_list_name = $autoisolir ? $autoisolir->address_list_isolir : 'none';

        LogHelper::logInfo(null, [
            'router_id' => $router->id,
            'simpleque_id' => $simpleque_id,
            'arp_id' => $arp_id,
            'ip_address' => $ip_address,
            'address_list' => $address_list_name
        ], 200, "Dispatch DeleteIpStaticJob", $traceId);

        dispatch(new DeleteIpStaticJob(
            $router,
            $simpleque_id,
            $arp_id,
            $ip_address,
            $address_list_name,
            $traceId
        ))->onQueue('default');
    }

    protected function softDeleteCustomer(Customer $customer, string $traceId)
    {
        LogHelper::logInfo(null, ['customer_id' => $customer->id], 200, "Soft delete process started", $traceId);

        $customer->customerSubscriptions->each(function ($subscription) use ($traceId) {
            LogHelper::logInfo(null, ['subscription_id' => $subscription->id], 200, "Soft deleting subscription", $traceId);

            $subscription->billingSubscriptionPeriodes->each(function ($periode) use ($traceId) {
                LogHelper::logInfo(null, ['periode_id' => $periode->id], 200, "Soft deleting billing periode", $traceId);
                $periode->delete();
            });

            $subscription->delete();
        });

        $customer->update([
            'activation_date' => null,
            'disabled' => 'true',
            'activation' => 'false'
        ]);

        $customer->delete();

        LogHelper::logInfo(null, ['customer_id' => $customer->id], 200, "Customer soft deleted successfully", $traceId);

        return true;
    }
}
