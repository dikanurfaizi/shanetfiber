<?php

namespace App\Services\Customers;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerAddon;
use App\Models\Membership\CustomerMembership;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Services\Routerboard\TestConnectionService;
use App\Services\Billings\BillingUnpaidService;
use App\Jobs\Secrets\DeletePppSecretJob;
use App\Jobs\Secrets\DeleteIpStaticJob;
use Illuminate\Support\Facades\DB;
use Throwable;

class CustomerDeletionService
{
    public function __construct(
        private TestConnectionService $testConnectionService,
        private BillingUnpaidService $billingUnpaidService
    ) {}

    /**
     * Memeriksa kelayakan penghapusan customer.
     */
    public function checkEligibility(Customer $customer, string $traceId, ?ExecutionTimer $timer = null): array
    {
        $timer ??= new ExecutionTimer();
        $hasUnpaidBills = $this->billingUnpaidService->hasUnpaidBills($customer->id, $traceId);

        if ($hasUnpaidBills) {
            LogHelper::logWarning(
                null,
                [
                    'customer_id'   => $customer->id,
                    'customer_name' => $customer->name,
                ],
                400,
                'Customer deletion blocked due to unpaid bills.',
                $traceId,
                $timer->lap()
            );

            return [
                'eligible'       => false,
                'redirect_route' => 'billings.index',
                'redirect_data'  => [
                    'search_name'   => $customer->name,
                    'source'        => 'customer-delete',
                    'customer_name' => $customer->name,
                ],
                'message'        => 'Customer memiliki tagihan aktif. Harap konfirmasi status billing sebelum melanjutkan penghapusan.',
            ];
        }

        LogHelper::logInfo(
            null,
            ['customer_id' => $customer->id],
            200,
            'Customer eligibility check passed.',
            $traceId,
            $timer->lap()
        );

        return [
            'eligible'       => true,
            'redirect_route' => 'customers.delete',
            'redirect_data'  => ['customer' => $customer->slug ?? $customer->id],
        ];
    }

    /**
     * Memeriksa kelayakan dan mengeksekusi penghapusan customer jika layak.
     */
    public function processCustomerDeletion(Customer $customer, string $traceId, ?ExecutionTimer $timer = null): array
    {
        $timer ??= new ExecutionTimer();

        LogHelper::logInfo(
            null,
            [
                'customer_id' => $customer->id,
                'activation'  => $customer->activation,
                'service'     => $customer->internet_service,
            ],
            200,
            'Processing customer deletion.',
            $traceId,
            $timer->lap()
        );

        try {
            $isActivated = filter_var($customer->activation, FILTER_VALIDATE_BOOLEAN);

            if ($isActivated) {
                $router = Mikrotik::findOrFail($customer->mikrotik_id);

                $this->ensureRouterOnline($router, $traceId, $timer);
                $this->dispatchNetworkCleanup($customer, $router, $traceId);
            }

            $this->terminateCustomer($customer, $isActivated);

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'is_activated' => $isActivated,
                ],
                200,
                'Customer deletion process completed successfully.',
                $traceId,
                $timer->lap()
            );

            return [
                'success' => true,
                'message' => 'Customer successfully deleted',
            ];
        } catch (Throwable $e) {
            LogHelper::logError(
                $e,
                [
                    'customer_id'  => $customer->id,
                    'mikrotik_id'  => $customer->mikrotik_id ?? null,
                    'exception'    => get_class($e),
                    'message'      => $e->getMessage(),
                    'file'         => $e->getFile(),
                    'line'         => $e->getLine(),
                ],
                500,
                'Failed during customer deletion process.',
                $traceId,
                $timer->lap()
            );

            return [
                'success' => false,
                'message' => $e->getMessage() ?: 'Gagal menghapus customer, terjadi kesalahan sistem.',
            ];
        }
    }

    private function ensureRouterOnline(Mikrotik $router, string $traceId, ExecutionTimer $timer): void
    {
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            return;
        }

        try {
            $this->testConnectionService->checkOnline($router, $traceId);
        } catch (Throwable $e) {
            LogHelper::logError(
                $e,
                [
                    'router_id'   => $router->id,
                    'router_name' => $router->name ?? null,
                    'host'        => $router->host ?? null,
                    'exception'   => get_class($e),
                    'message'     => $e->getMessage(),
                ],
                503,
                'Router offline during delete customer check.',
                $traceId,
                $timer->lap()
            );

            throw new \Exception('Router offline, silakan periksa koneksi router Anda.');
        }
    }

    private function dispatchNetworkCleanup(Customer $customer, Mikrotik $router, string $traceId): void
    {
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            return;
        }

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
                AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->value('address_list_isolir') ?? 'none',
                $traceId
            ))->onQueue('default'),

            default => null,
        };
    }

    private function terminateCustomer(Customer $customer, bool $expired): void
    {
        DB::transaction(function () use ($customer, $expired) {
            $now = now();

            $deactivateActiveRelation = function ($query, $customer) use ($now) {
                $query->where('customer_id', $customer->id)
                    ->where('is_active', true)
                    ->update([
                        'is_active'  => false,
                        'end_date'   => $now,
                        'updated_at' => $now,
                    ]);
            };

            // 1. Nonaktifkan Membership & Addons
            $deactivateActiveRelation(CustomerMembership::query(), $customer);
            $deactivateActiveRelation(CustomerAddon::query(), $customer);

            // 2. Handle Subscriptions & Billing Periodes
            $customer->customerSubscriptions->each(function ($subscription) use ($expired, $now) {
                $subscription->billingSubscriptionPeriodes->each(function ($periode) use ($expired) {
                    if ($expired) {
                        $periode->update(['status' => 'expired']);
                    }
                    $periode->delete();
                });

                if ($expired) {
                    $subscription->update([
                        'status'        => 'terminated',
                        'terminated_at' => $now,
                    ]);
                }

                $subscription->delete();
            });

            // 3. Update State & Soft Delete Customer
            $customer->update([
                'activation_date' => null,
                'is_active'       => false,
                'activation'      => false,
            ]);

            $customer->delete();
        });
    }
}
