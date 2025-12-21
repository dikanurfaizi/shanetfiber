<?php

namespace App\Jobs\Network;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Services\Routerboard\SystemService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Helpers\LogHelper;

class FetchMikrotikInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $traceId;

    public function __construct(string $traceId)
    {
        $this->traceId = $traceId;
    }

    public function handle(): void
    {
        $customers = Customer::select(
            'id',
            'name',
            'mikrotik_id',
            'username',
            'mac_address',
            'interface',
            'ip_address',
            'secret_id'
        )->get();

        if ($customers->isEmpty()) {
            LogHelper::logInfo(
                null,
                [],
                200,
                'FetchMikrotikInfo skipped (no customers)',
                $this->traceId
            );
            return;
        }

        $customersByRouter = $customers->groupBy('mikrotik_id');

        /** @var SystemService $systemService */
        $systemService = app(SystemService::class);

        /** @var SecretService $secretService */
        $secretService = app(SecretService::class);

        foreach ($customersByRouter as $routerId => $routerCustomers) {

            $router = Mikrotik::find($routerId);

            if (!$router) {
                LogHelper::logWarning(
                    null,
                    [
                        'mikrotik_id'      => $routerId,
                        'customer_count'  => $routerCustomers->count(),
                    ],
                    404,
                    'Mikrotik not found for customers',
                    $this->traceId
                );
                continue;
            }

            $summary = [
                'total_customers'        => $routerCustomers->count(),
                'updated'                => 0,
                'skipped_null_response'  => 0,
                'ppp_not_found'          => 0,
                'interface_not_found'    => 0,
            ];

            try {
                $interfaces  = collect(
                    $systemService->mikrotikInterface($router, $this->traceId)
                );

                $pppActives  = collect(
                    $secretService->getPppActive($router, $this->traceId)
                );

                foreach ($routerCustomers as $customer) {

                    $ppp = $pppActives->first(
                        fn($p) => ($p['name'] ?? null) === $customer->username
                    );

                    if (!$ppp) {
                        $summary['ppp_not_found']++;

                        LogHelper::logWarning(
                            null,
                            [
                                'router_id'   => $router->id,
                                'customer_id' => $customer->id,
                                'username'    => $customer->username,
                            ],
                            404,
                            'PPP Active not found',
                            $this->traceId
                        );

                        continue;
                    }

                    $iface = $interfaces->first(
                        fn($i) => ($i['.id'] ?? null) === $customer->secret_id
                    );

                    if (!$iface) {
                        $summary['interface_not_found']++;
                    }

                    $shouldUpdate = false;

                    if (!empty($ppp['caller-id'])) {
                        $customer->mac_address = $ppp['caller-id'];
                        $shouldUpdate = true;
                    }

                    if (!empty($ppp['address'])) {
                        $customer->ip_address = $ppp['address'];
                        $shouldUpdate = true;
                    }

                    if (!empty($iface['name'])) {
                        $customer->interface = $iface['name'];
                        $shouldUpdate = true;
                    }

                    if ($shouldUpdate) {
                        $customer->save();
                        $summary['updated']++;
                    } else {
                        $summary['skipped_null_response']++;
                    }
                }

                // ✅ SINGLE SUMMARY LOG PER ROUTER
                LogHelper::logInfo(
                    null,
                    [
                        'router_id'   => $router->id,
                        'router_name' => $router->name ?? null,
                        'summary'     => $summary,
                    ],
                    200,
                    'FetchMikrotikInfo completed',
                    $this->traceId
                );
            } catch (\Throwable $e) {

                LogHelper::logError(
                    null,
                    [
                        'router_id'      => $router->id,
                        'error_message'  => $e->getMessage(),
                        'customer_count' => $routerCustomers->count(),
                    ],
                    500,
                    'FetchMikrotikInfo failed for router',
                    $this->traceId
                );
            }
        }
    }
}
