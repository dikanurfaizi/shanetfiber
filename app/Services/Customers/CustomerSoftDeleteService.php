<?php

namespace App\Services\Customers;

use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Services\Routerboard\TestConnectionService;
use App\Jobs\Secrets\DeletePppSecretJob;
use App\Jobs\Secrets\DeleteIpStaticJob;
use App\Models\Customers\CustomerAddon;
use App\Models\Membership\CustomerMembership;
use Illuminate\Support\Facades\DB;

class CustomerSoftDeleteService
{
    private TestConnectionService $testConnectionService;

    public function __construct(TestConnectionService $testConnectionService)
    {
        $this->testConnectionService = $testConnectionService;
    }

    public function deleteCustomer(Customer $customer, string $traceId): bool
    {
        LogHelper::logInfo(
            null,
            ['customer_id' => $customer->id],
            200,
            'Delete customer requested',
            $traceId
        );

        if ($customer->activation !== 'true') {
            $this->terminateCustomer($customer, false);
            return true;
        }

        $this->ensureRouterOnline($customer, $traceId);
        $this->dispatchNetworkCleanup($customer, $traceId);
        $this->terminateCustomer($customer, true);

        return true;
    }

    private function ensureRouterOnline(Customer $customer, string $traceId): void
    {
        $router = Mikrotik::findOrFail($customer->mikrotik_id);

        try {
            if (config('services.shanetfiber.allow_mikrotik_actions')) {
                $this->testConnectionService->checkOnline($router, $traceId);
            }
        } catch (\Throwable $e) {
            LogHelper::logError(
                $e,
                ['router_id' => $router->id],
                503,
                'Router offline during delete customer',
                $traceId
            );

            throw new \Exception('Router offline, please check your router.');
        }
    }

    private function dispatchNetworkCleanup(Customer $customer, string $traceId): void
    {
        $router = Mikrotik::find($customer->mikrotik_id);
        if (config('services.shanetfiber.allow_mikrotik_actions')) {
            match ($customer->internet_service) {
                'ppp' => dispatch(new DeletePppSecretJob(
                    $router,
                    $customer->username,
                    $traceId
                ))->onQueue('default'),

                'ip_static' => dispatch(new DeleteIpStaticJob(
                    $router,
                    $customer->simpleque_id,
                    $customer->arp_id,
                    $customer->ip_static,
                    optional(
                        AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first()
                    )->address_list_isolir ?? 'none',
                    $traceId
                ))->onQueue('default'),

                default => null,
            };
        }
    }

    private function terminateCustomer(Customer $customer, bool $expired): void
    {
        DB::transaction(function () use ($customer, $expired) {

            // 1. Tutup semua membership aktif
            CustomerMembership::where('customer_id', $customer->id)
                ->where('is_active', true)
                ->update([
                    'is_active'  => false,
                    'end_date'   => now(),
                    'updated_at' => now(),
                ]);

            // 1.5. NONAKTIFKAN semua customer addons
            CustomerAddon::where('customer_id', $customer->id)
                ->where('is_active', true)
                ->update([
                    'is_active'  => false,
                    'end_date'   => now(),
                    'updated_at' => now(),
                ]);

            // 2. Handle subscription & billing
            $customer->customerSubscriptions->each(function ($subscription) use ($expired) {

                $subscription->billingSubscriptionPeriodes->each(function ($periode) use ($expired) {
                    if ($expired) {
                        $periode->update(['status' => 'expired']);
                    }
                    $periode->delete();
                });

                if ($expired) {
                    $subscription->update([
                        'status'        => 'terminated',
                        'terminated_at' => now(),
                    ]);
                }

                $subscription->delete();
            });

            // 3. Update customer state
            $customer->update([
                'activation_date' => null,
                'disabled'        => 'true',
                'activation'      => 'false',
            ]);

            // 4. Soft delete customer
            $customer->delete();
        });
    }
}
