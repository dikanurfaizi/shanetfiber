<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Mikrotik;
use App\Models\Paket\PppProfile;
use App\Services\Routerboard\Ppp\SecretService;
use App\Helpers\LogHelper;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Str;

class CreatePppProfileToRouter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected int $routerId;
    protected int $profileId;
    protected string $traceId;

    public function __construct(int $routerId, int $profileId, ?string $traceId = null)
    {
        $this->routerId  = $routerId;
        $this->profileId = $profileId;
        $this->traceId = $traceId ?? (string) Str::uuid();
    }

    public function handle(SecretService $secretService): void
    {
        $routerId  = $this->routerId;
        $profileId = $this->profileId;
        $router = Mikrotik::find($routerId);
        $profile = PppProfile::find($profileId);

        /** ------------------------------------------------------------
         * 1)  Cek apakah profile sudah ada di Mikrotik
         *     ➜ gunakan getPppProfileByName() — ringan
         * ----------------------------------------------------------- */
        try {
            $exists = $secretService
                ->getPppProfileByName($router, $profile->profile_name);
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'name' => $router->name,
                    'host' => $router->host,
                    'error' => $e->getMessage()
                ],
                500,
                'Router Offline',
                $this->traceId
            );
            return;
        }

        if (is_string($exists) && trim(strtolower($exists)) === 'error') {
            // Anggap sebagai error, tidak lanjut
            LogHelper::logError(
                null,
                [
                    'name' => $router->name,
                    'host' => $router->host,
                    'error' => $exists
                ],
                500,
                'Terjadi error',
                $this->traceId
            );
            return;
        }

        if (!empty($exists)) {
            LogHelper::logWarning(
                null,
                [
                    'name' => $router->name,
                    'host' => $router->host,
                    'profile_name' => $profile->profile_name
                ],
                200,
                'Profile Already Exists',
                $this->traceId
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
            $this->traceId
        );

        /** ------------------------------------------------------------
         * 2)  Siapkan data & panggil createPppProfile
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

        $response = $secretService->createPppProfile($router, $data);

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
            $this->traceId
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
                $this->traceId
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
            $this->traceId
        );
    }
}
