<?php

namespace App\Services;

use App\Models\Mikrotik;
use App\Models\Paket\PppProfile;
use App\Models\AutoIsolir;
use App\Services\Routerboard\ScriptService;
use App\Services\Routerboard\ScheduleService;
use App\Services\Routerboard\TestConnectionService;
use App\Services\Routerboard\FirewallService;
use App\Services\Routerboard\ProxyService;

class AutoIsolirService
{
    protected $scriptService;
    protected $scheduleService;
    protected $testConnectionService;
    protected $firewallService;
    protected $proxyService;

    public function __construct(
        ScriptService $scriptService,
        ScheduleService $scheduleService,
        TestConnectionService $testConnectionService,
        FirewallService $firewallService,
        proxyService $proxyService
    ) {
        $this->scriptService = $scriptService;
        $this->scheduleService = $scheduleService;
        $this->testConnectionService = $testConnectionService;
        $this->firewallService = $firewallService;
        $this->proxyService = $proxyService;
    }

    public function checkConnection($router, string $traceId): bool
    {
        $result = $this->testConnectionService->checkOnline($router, $traceId);

        if (empty($result) || !is_array($result)) {
            throw new \Exception('Mikrotik ' . $router  . ' offline, please check your router.');
        }
        return true;
    }

    public function createAutoIsolir($request, string $traceId)
    {
        $mikrotikId = $request->mikrotik_id;
        $router = Mikrotik::findOrFail($mikrotikId);

        // Cek koneksi router
        $this->checkConnection($router, $traceId);

        $profile = PppProfile::where('id', $request->profile_id)->value('profile_name');
        $nameScript = $nameSchedule = $request->name;
        $activationDate = $request->activation_date;
        $dueDate = $activationDate === 'false' ? $request->due_date : null;
        $rosVersion = $router->ros_version_id;
        $addressList = $request->address_list_isolir;

        // Generate script
        if ($activationDate === 'true') {
            $script = $this->scriptService->addAutoisolirActivationDate(
                $router,
                $rosVersion,
                $nameScript,
                $request->comment_unpayment,
                $profile,
                $addressList,
                $traceId
            );
        } else {
            $script = $this->scriptService->addAutoisolirDueDate(
                $router,
                $rosVersion,
                $nameScript,
                $dueDate,
                $request->comment_unpayment,
                $profile,
                $addressList,
                $traceId
            );
        }

        if (isset($script['after']['message'])) {
            throw new \Exception(
                $script['after']['message'] == 'failure: item with such name already exists'
                    ? 'Script with such name already exists'
                    : 'An error occurred while creating the script: ' . $script['after']['message']
            );
        }

        $scriptId = $script['after']['ret'];

        // Tambahkan jadwal
        $schedule = $this->scheduleService->addScheduleEveryDay($router, $nameSchedule, $nameScript, $traceId);

        if (isset($schedule['after']['message'])) {
            $this->scriptService->removeScript($router, $scriptId, $traceId);
            throw new \Exception(
                $schedule['after']['message'] == 'failure: item with this name already exists'
                    ? 'Schedule with such name already exists'
                    : 'An error occurred while creating the schedule: ' . $schedule['after']['message']
            );
        }

        $scheduleId = $schedule['after']['ret'];

        // Simpan ke database
        AutoIsolir::create([
            'name' => $request->name,
            'mikrotik_id' => $mikrotikId,
            'profile_id' => $request->profile_id,
            'script_id' => $scriptId,
            'schedule_id' => $scheduleId,
            'activation_date' => $activationDate,
            'comment_payment' => $request->comment_payment,
            'comment_unpayment' => $request->comment_unpayment,
            'due_date' => $dueDate,
            'disabled' => 'false',
            'nat_id' => 0,
            'proxy_access_id' => 0,
            'address_list_isolir' => $addressList,
        ]);
    }

    public function updateAutoIsolir(array $data, AutoIsolir $autoisolir, string $traceId)
    {
        $router = $autoisolir->mikrotik;
        $this->checkConnection($router, $traceId);
        $profileName = PppProfile::find($data['profile_id'])?->profile_name;
        $rosVersion = $router->ros_version_id;
        $scriptId = $autoisolir->script_id;
        $dueDate = $data['activation_date'] === 'false' ? $data['due_date'] : null;

        $response = $data['activation_date'] === 'true'
            ? $data['scriptService']->setAutoisolirActivationDate($router, $rosVersion, $scriptId, $data['comment_unpayment'], $profileName, $data['address_list_isolir'])
            : $data['scriptService']->setAutoisolirDueDate($router, $rosVersion, $scriptId, $dueDate, $data['comment_unpayment'], $profileName, $data['address_list_isolir']);

        if (isset($response['after']['message'])) {
            throw new \Exception($this->mapScriptError($response['after']['message']));
        }

        $autoisolir->update([
            'profile_id' => $data['profile_id'],
            'comment_payment' => $data['comment_payment'],
            'comment_unpayment' => $data['comment_unpayment'],
            'due_date' => $dueDate,
            'activation_date' => $data['activation_date'],
            'address_list_isolir' => $data['address_list_isolir'],
        ]);
    }

    private function mapScriptError(string $rawMessage): string
    {
        return match (true) {
            str_contains($rawMessage, 'policy') => 'Akses API user tidak memiliki izin untuk mengedit script. Harap periksa policy user di Mikrotik.',
            str_contains($rawMessage, 'such name already exists') => 'Nama script sudah digunakan di Mikrotik. Silakan gunakan nama lain.',
            default => 'Terjadi kesalahan saat menjalankan perintah Mikrotik: ' . $rawMessage,
        };
    }

    // =========================== UPDATE NAT PROXY ===========================

    public function updateNatProxy(AutoIsolir $autoisolir, array $data, string $traceId): void
    {
        $router = $autoisolir->mikrotik;

        if ($router->disabled === 'true') {
            throw new \Exception('Router is disabled. Enable it first.');
        }

        $nat_dst_address = $data['nat_dst_address'];
        $nat_dst_address_list = $data['nat_dst_address_list'] ?? 'ALLOW_ACCESS_ISOLIR';
        $nat_src_address_list = $autoisolir->address_list_isolir;
        $proxy_access_src_address = $data['proxy_access_src_address'];
        $action = $data['access_action'];
        $rosVersion = $router->ros_version_id;

        if ($autoisolir->nat_id == 0) {
            $create = $this->firewallService->createNatService($router, $nat_dst_address, $nat_src_address_list, $nat_dst_address_list, $traceId);
            if (isset($create['after']['message'])) {
                $natId = 0;
            } else {
                $natId = $create['after']['ret'];
                $autoisolir->update(['nat_id' => $natId]);
            }
        } else {
            $this->firewallService->updateNatService($router, $nat_dst_address, $nat_src_address_list, $nat_dst_address_list, $autoisolir->nat_id, $traceId);
        }

        if ($autoisolir->proxy_access_id == 0) {
            $create = $rosVersion === '1'
                ? $this->proxyService->createAccessServiceV6_49($router, $proxy_access_src_address, $nat_dst_address, $action, $traceId)
                : $this->proxyService->createAccessServiceV7_10($router, $proxy_access_src_address, $nat_dst_address, $action, $traceId);

            if (isset($create['after']['message'])) {
                throw new \Exception($create['after']['message']);
            }

            $autoisolir->update(['proxy_access_id' => $create['after']['ret']]);
        } else {
            if ($rosVersion === '1') {
                $this->proxyService->updateAccessServiceV6_49($router, $proxy_access_src_address, $nat_dst_address, $autoisolir->proxy_access_id, $action, $traceId);
            } else {
                $this->proxyService->updateAccessServiceV7_10($router, $proxy_access_src_address, $nat_dst_address, $autoisolir->proxy_access_id, $action, $traceId);
            }
        }

        $this->proxyService->enableProxyService($router, $traceId);

        $autoisolir->update([
            'nat_dst_address' => $nat_dst_address,
            'nat_dst_address_list' => $nat_dst_address_list,
            'proxy_access_src_address' => $proxy_access_src_address,
            'proxy_access_action_data' => $nat_dst_address . '/isolir',
            'proxy_access_action' => $action,
        ]);
    }
}
