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

class CreatePppProfileToRouter implements ShouldQueue
{
    protected $router;
    protected $profile;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $router, PppProfile $profile)
    {
        $this->router = $router;
        $this->profile = $profile;
    }

    /**
     * Execute the job.
     */
    public function handle(SecretService $secretService)
    {
        $router = $this->router;
        $profile = $this->profile;

        // Check if profile already exists in Mikrotik
        try {
            $existingProfiles = collect($secretService->getPppProfile($router))->pluck('name')->toArray();
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Router Offline');
            return;
        }

        if (in_array($profile->profile_name, $existingProfiles)) {
            LogHelper::logWarning(null, ['profile_name' => $profile->profile_name], 200, 'Profile Already Exists');
            return;
        }

        LogHelper::logInfo(null, ['profile_name' => $profile->profile_name], 200, 'Exporting Profile');

        // Prepare data for Mikrotik API
        $data = [
            'name' => $profile->profile_name,
            'bridge-learning' => 'default',
            'use-ipv6' => 'yes',
            'use-mpls' => 'default',
            'use-compression' => 'default',
            'use-encryption' => $profile->use_encryption,
            'only-one' => 'yes',
            'change-tcp-mss' => 'default',
            'use-upnp' => 'default',
            'rate-limit' => $profile->rate_limit,
            'address-list' => '',
            'dns-server' => $profile->dns_server,
            'on-up' => '',
            'on-down' => '',
            'queue-type' => 'default-small/default-small',
            'comment' => $profile->comment,
        ];

        $response = $secretService->createPppProfile($router, $data);

        LogHelper::logInfo(null, ['profile' => $data['name'], 'response' => $response], 200, 'PPP Profile export response');

        if (isset($response['after']['message']) || !isset($response['after']['ret'])) {
            LogHelper::logError(null, ['profile' => $data['name'], 'error' => $response['after']['message'] ?? 'Unknown error'], 500, 'Error exporting profile');
            return;
        }

        LogHelper::logInfo(null, ['profile_name' => $profile->profile_name], 200, 'Profile Exported Successfully');
    }
}
