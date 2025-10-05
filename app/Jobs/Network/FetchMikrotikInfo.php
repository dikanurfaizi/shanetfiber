<?php

namespace App\Jobs\Network;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Customer;
use App\Models\Mikrotik;
use App\Services\Routerboard\SystemService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Helpers\LogHelper;

class FetchMikrotikInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $traceId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $traceId)
    {
        $this->traceId = $traceId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Ambil semua customer yang perlu diproses
        $customers = Customer::select('id', 'mikrotik_id', 'username', 'mac_address', 'interface', 'ip_address', 'secret_id')
            ->get();

        // Kelompokkan customer berdasarkan router
        $routersGrouped = $customers->groupBy('mikrotik_id');

        /** @var SystemService $systemService */
        $systemService = app()->make(SystemService::class);

        /** @var SecretService $secretService */
        $secretService = app()->make(SecretService::class);

        foreach ($routersGrouped as $routerId => $routerCustomers) {
            $router = Mikrotik::find($routerId);

            if (!$router) {
                foreach ($routerCustomers as $c) {
                    LogHelper::logWarning(null, [
                        'mikrotik_id' => $routerId,
                        'customer_id' => $c->id
                    ], 404, "Mikrotik ID not found", $this->traceId);
                }
                continue;
            }

            try {
                // Ambil semua interface di router
                $interfaces = $systemService->mikrotikInterface($router, $this->traceId);

                // Log seluruh isi interfaces agar bisa dilihat
                LogHelper::logInfo(
                    null,
                    ['router_id' => $router->id, 'interfaces' => $interfaces],
                    200,
                    "Debug: full interface response from router",
                    $this->traceId
                );

                // Ambil semua PPP active
                $pppActives = $secretService->getPppActive($router, $this->traceId);

                LogHelper::logInfo(
                    null,
                    ['router_id' => $router->id, 'pppActives' => $pppActives],
                    200,
                    "Debug: full pppActives response from router",
                    $this->traceId
                );

                // Mapping berdasarkan username
                foreach ($routerCustomers as $customer) {
                    // Cari interface yang sesuai username (misal name = username)
                    $iface = collect($interfaces)->first(fn($i) => ($i['.id'] ?? null) === $customer->secret_id);
                    // $macAddress = $iface['mac-address'] ?? null;
                    $interfaceName = $iface['name'] ?? null;

                    // Cari IP address dari PPP active
                    $ppp = collect($pppActives)->first(fn($p) => ($p['name'] ?? null) === $customer->username);
                    $ipAddress = $ppp['address'] ?? null;
                    $macAddress = $ppp['caller-id'] ?? null;

                    // Update customer
                    $customer->mac_address = $macAddress ?? '';
                    $customer->interface = $interfaceName ?? '';
                    $customer->ip_address = $ipAddress ?? '';
                    $customer->save();

                    LogHelper::logInfo(null, [
                        'customer_id' => $customer->id,
                        'mac_address' => $macAddress,
                        'interface' => $interfaceName,
                        'ip_address' => $ipAddress
                    ], 200, "Customer updated successfully", $this->traceId);
                }
            } catch (\Exception $e) {
                foreach ($routerCustomers as $c) {
                    LogHelper::logError(null, [
                        'customer_id' => $c->id,
                        'error_message' => $e->getMessage()
                    ], 500, "Failed to fetch interfaces & IP", $this->traceId);
                }
            }
        }
    }
}
