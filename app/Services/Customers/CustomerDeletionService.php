<?php

namespace App\Services\Customers;

use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Jobs\Secrets\DeletePppSecretJob;
use App\Jobs\Secrets\DeleteIpStaticJob;
use App\Models\CustomerSubscription;
use App\Services\Routerboard\TestConnectionService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\IpStaticService;

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
        if ($customer->activation !== 'true') {
            return $this->softDeleteCustomer($customer);
        }

        $router = Mikrotik::find($customer->mikrotik_id);
        $autoisolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

        try {
            $this->testConnectionService->checkOnline($router, $traceId);
        } catch (\Exception $e) {
            throw new \Exception('Router offline, please check your router.');
        }

        if ($customer->internet_service === 'ppp') {
            $this->deletePPP($customer, $router, $traceId);
        } elseif ($customer->internet_service === 'ip_static') {
            $this->deleteIpStatic($customer, $router, $autoisolir, $traceId);
        }

        return $this->softDeleteCustomer($customer);
    }

    protected function deletePPP(Customer $customer, $router, string $traceId)
    {
        $username = $customer->username;

        dispatch(new DeletePppSecretJob(
            $router,       // instance Mikrotik
            $customer->username,
            $traceId // trace id bebas
        ))->onQueue('default');
    }

    protected function deleteIpStatic(Customer $customer, $router, $autoisolir = null, string $traceId)
    {
        $simpleque_id = $customer->simpleque_id;
        $ip_address = $customer->ip_static;
        $arp_id = $customer->arp_id;
        $address_list_name = $autoisolir ? $autoisolir->address_list_isolir : 'none';

        dispatch(new DeleteIpStaticJob(
            $router,
            $simpleque_id,
            $arp_id,
            $ip_address,
            $address_list_name,
            $traceId
        ))->onQueue('default');
    }

    protected function softDeleteCustomer(Customer $customer)
    {
        // Soft delete semua subscription
        $customer->customerSubscriptions->each(function ($subscription) {
            // Soft delete semua billing periodes
            $subscription->billingSubscriptionPeriodes->each(function ($periode) {
                $periode->delete();
            });

            $subscription->delete();
        });

        $customer->update([
            'activation_date' => null,
            'disabled' => 'true',
            'activation' => 'false'
        ]);

        // Soft delete customer
        $customer->delete();

        return true;
    }
}
