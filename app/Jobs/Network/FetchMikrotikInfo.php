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

                foreach ($routerCustomers as $customer) {
                    // Cari interface berdasarkan secret_id
                    $iface = collect($interfaces)->first(fn($i) => ($i['.id'] ?? null) === $customer->secret_id);

                    // Ambil data dari interface dan ppp active
                    $interfaceName = $iface['name'] ?? null;
                    $ppp = collect($pppActives)->first(fn($p) => ($p['name'] ?? null) === $customer->username);
                    $ipAddress = $ppp['address'] ?? null;
                    $macAddress = $ppp['caller-id'] ?? null;

                    // --- Validasi agar tidak menimpa data lama dengan NULL ---
                    $shouldUpdate = false;

                    if (!is_null($macAddress) && $macAddress !== '') {
                        $customer->mac_address = $macAddress;
                        $shouldUpdate = true;
                    }
                    if (!is_null($interfaceName) && $interfaceName !== '') {
                        $customer->interface = $interfaceName;
                        $shouldUpdate = true;
                    }
                    if (!is_null($ipAddress) && $ipAddress !== '') {
                        $customer->ip_address = $ipAddress;
                        $shouldUpdate = true;
                    }

                    // Hanya save jika memang ada data baru yang valid
                    if ($shouldUpdate) {
                        $customer->save();
                        LogHelper::logInfo(
                            null,
                            [
                                'customer_id'  => $customer->id,
                                'customer_name' => $customer->name,
                                'mac_address'  => $macAddress,
                                'interface'    => $interfaceName,
                                'ip_address'   => $ipAddress
                            ],
                            200,
                            "Customer updated successfully",
                            $this->traceId
                        );
                    } else {
                        LogHelper::logInfo(
                            null,
                            [
                                'customer_id'  => $customer->id,
                                'customer_name' => $customer->name,
                                'mac_address'  => $customer->mac_address,
                                'interface'    => $customer->interface,
                                'ip_address'   => $customer->ip_address
                            ],
                            200,
                            "Skipped update (response null values)",
                            $this->traceId
                        );
                    }
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
