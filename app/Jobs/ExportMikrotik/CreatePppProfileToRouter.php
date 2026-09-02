<?php

namespace App\Jobs\ExportMikrotik;

use App\Helpers\ExecutionTimer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Mikrotik;
use App\Models\Packages\PppProfile;
use App\Services\Routerboard\Ppp\SecretService;
use App\Helpers\LogHelper;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Str;

class CreatePppProfileToRouter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected Mikrotik $router;
    protected PppProfile $profile;
    protected string $traceId;

    public function __construct(Mikrotik $router, PppProfile $profile, ?string $traceId = null)
    {
        $this->router  = $router;
        $this->profile = $profile;
        $this->traceId = $traceId ?? (string) Str::uuid();
    }

    public function handle(SecretService $secretService): void
    {
        $timer = new ExecutionTimer();

        $router  = $this->router;
        $profile = $this->profile;

        LogHelper::logInfo(
            null,
            [
                'routerId' => $router->id,
                'profileId' => $profile->id,
                'traceId' => $this->traceId
            ],
            200,
            '[CreatePppProfileToRouter] Job started',
            $this->traceId,
            $timer->lap()
        );

        /** ------------------------------------------------------------
         * 1) Cek apakah profile sudah ada di Mikrotik
         * ----------------------------------------------------------- */
        $exists = false;
        try {
            $result = $secretService->getPppProfileByName($router, $profile->profile_name, $this->traceId);

            // Jika status = success ➜ berarti profile SUDAH ADA
            if (is_array($result) && ($result['status'] ?? null) === 'success') {
                $exists = true;
            }
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();

            // Jika error mengandung pesan 'not found', abaikan karena memang profil belum ada
            if (str_contains(strtolower($errorMessage), 'not found')) {
                $exists = false;
            } else {
                // Jika error koneksi asli (timeout, connection refused, dsb)
                LogHelper::logError(
                    null,
                    [
                        'name' => $router->name,
                        'host' => $router->host,
                        'error' => $errorMessage
                    ],
                    500,
                    'Router Offline / Communication Error',
                    $this->traceId,
                    $timer->lap()
                );
                return;
            }
        }

        // Jika profil SUDAH ADA di Mikrotik, hentikan proses ekspor
        if ($exists) {
            LogHelper::logWarning(
                null,
                [
                    'name' => $router->name,
                    'host' => $router->host,
                    'profile_name' => $profile->profile_name
                ],
                200,
                'Profile Already Exists',
                $this->traceId,
                $timer->lap()
            );
            return;
        }

        LogHelper::logInfo(
            null,
            [
                'name' => $router->name,
                'host' => $router->host,
                'profile_name' => $profile->profile_name
            ],
            200,
            'Exporting Profile',
            $this->traceId,
            $timer->lap()
        );

        /** ------------------------------------------------------------
         * 2) Siapkan data & panggil createPppProfile
         * ----------------------------------------------------------- */
        $data = [
            'name'              => $profile->profile_name,
            'bridge-learning'   => 'default',
            'use-ipv6'          => 'yes',
            'use-mpls'          => 'default',
            'use-compression'   => 'default',
            'use-encryption'    => $profile->use_encryption,
            'only-one'          => 'yes',
            'change-tcp-mss'    => 'default',
            'use-upnp'          => 'default',
            'rate-limit'        => $profile->rate_limit,
            'address-list'      => '',
            'dns-server'        => $profile->dns_server,
            'on-up'             => '',
            'on-down'           => '',
            'queue-type'        => 'default-small/default-small',
            'comment'           => $profile->comment,
        ];

        try {
            $response = $secretService->createPppProfile($router, $data, $this->traceId);

            LogHelper::logInfo(
                null,
                [
                    'name' => $router->name,
                    'host' => $router->host,
                    'profile' => $data['name'],
                    'response' => $response
                ],
                200,
                'PPP Profile export response',
                $this->traceId,
                $timer->lap()
            );

            if (
                isset($response['after']['message']) ||
                !isset($response['after']['ret'])
            ) {
                LogHelper::logError(
                    null,
                    [
                        'name' => $router->name,
                        'host' => $router->host,
                        'profile' => $data['name'],
                        'error'   => $response['after']['message'] ?? 'Unknown error'
                    ],
                    500,
                    'Error exporting profile',
                    $this->traceId,
                    $timer->lap()
                );
                return;
            }

            LogHelper::logInfo(
                null,
                [
                    'name' => $router->name,
                    'host' => $router->host,
                    'profile_name' => $profile->profile_name
                ],
                200,
                'Profile Exported Successfully',
                $this->traceId,
                $timer->lap()
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'name' => $router->name,
                    'host' => $router->host,
                    'profile' => $data['name'],
                    'error'   => $e->getMessage()
                ],
                500,
                'Failed to execute createPppProfile',
                $this->traceId,
                $timer->lap()
            );
        }
    }
}
