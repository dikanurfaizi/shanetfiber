<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Mikrotik;
use App\Models\WhatsAppGateway;
use App\Models\Websystem;
use App\Helpers\LogHelper;
use Exception;
use RouterOS\Query;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\StreamException;

class UpdateRateLimitProfileSecretJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $router;
    protected $profileName;
    protected $rateLimit;

    public $tries = 5;
    public $timeout = 60;
    public $maxExceptions = 3;

    public function __construct(Mikrotik $router, string $profileName, string $rateLimit)
    {
        $this->router = $router;
        $this->profileName = $profileName;
        $this->rateLimit = $rateLimit;
    }

    public function handle()
    {
        $maxLocalRetries = 3;
        $attempt = 0;
        $timeToSendWhatsapp = $maxLocalRetries - 1;

        while ($attempt < $maxLocalRetries) {
            try {
                $this->updateRateLimitProfileSecret(
                    $this->router,
                    $this->rateLimit,
                    $this->profileName
                );

                return; // Berhasil, keluar
            } catch (Exception $e) {
                LogHelper::logWarning(null, [
                    'attempt' => $attempt,
                    'profile' => $this->profileName,
                    'router' => $this->router->name,
                    'error_message' => $e->getMessage()
                ], 500, "Failed to update rate limit");

                if ($attempt >= $timeToSendWhatsapp) {
                    $this->failed($e->getMessage());
                    throw $e;
                }

                $attempt++;

                sleep(10); // Retry setelah jeda
            }
        }
    }

    public function failed(string $message)
    {
        LogHelper::logError(null, [
            'profile' => $this->profileName,
            'router' => $this->router->name,
            'error_message' => $message
        ], 500, "UpdateRateLimitProfileSecretJob failed");

        $gateway = WhatsappGateway::where('slug', 'gateway')->first();
        $websystem = Websystem::first();

        if (!$websystem) {
            LogHelper::logError(null, [
                'profile' => $this->profileName,
                'router' => $this->router->name,
                'error_message' => 'Websystem data not found'
            ], 500, "Websystem data missing");
            return;
        }

        $operatorPhone = ltrim($websystem->phone ?? '');
        $customerName = $websystem->title ?? 'Admin';

        if (preg_match('/^(\+?62|08|8)/', $operatorPhone)) {
            $whatsappMessage = "❗ Gagal update rate limit PPP Profile `{$this->profileName}` di router `{$this->router->name}`. Error: {$message}";

            if ($gateway && $gateway->disabled === 'false') {
                dispatch(new SendWhatsappSingleJob($customerName, $operatorPhone, $gateway, $whatsappMessage));
            }
        } else {
            LogHelper::logError(null, [
                'profile' => $this->profileName,
                'router' => $this->router->name,
                'error_message' => 'Invalid phone number format'
            ], 500, "Invalid operator phone number");
        }
    }

    public function backoff()
    {
        return [30, 60, 120];
    }

    private function updateRateLimitProfileSecret($mikrotik, $rateLimit, $profileName)
    {
        $client = $this->getMikrotik($mikrotik);

        if (!$client) {
            throw new Exception('Failed to connect to Mikrotik.');
        }

        $profiles = $client->query(new Query('/ppp/profile/print'))->read();

        if (empty($profiles)) {
            throw new Exception("No profiles found in Mikrotik.");
        }

        $targetProfile = collect($profiles)->firstWhere('name', $profileName);

        if (!$targetProfile) {
            LogHelper::logWarning(null, [
                'host' => $mikrotik->host ?? 'Unknown Host',
                'name' => $mikrotik->name,
                'profile_name' => $profileName
            ], 404, "PPP Profile not found during rate limit update");

            $this->failed("Profile '{$profileName}' not found in Mikrotik '{$mikrotik->name}' with host '{$mikrotik->host}'");
            throw new Exception("Profile '{$profileName}' not found in Mikrotik '{$mikrotik->name}' with host '{$mikrotik->host}'");
        }

        $updateQuery = (new Query('/ppp/profile/set'))
            ->equal('.id', $targetProfile['.id'])
            ->equal('rate-limit', $rateLimit);

        return $client->query($updateQuery)->read();
    }

    private function getMikrotik($mikrotik): ?Client
    {
        try {
            $config = (new Config())
                ->set('timeout', 2)
                ->set('host', $mikrotik->host)
                ->set('port', (int) $mikrotik->port)
                ->set('user', $mikrotik->username)
                ->set('pass', $mikrotik->password);

            return new Client($config);
        } catch (StreamException $e) {
            LogHelper::logError(null, [
                'host' => $mikrotik->host ?? 'N/A',
                'port' => $mikrotik->port ?? 'N/A',
                'error' => 'Stream Timeout: ' . $e->getMessage()
            ], 504, "Failed to connect to Mikrotik (stream timeout)");
            throw $e;
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'host' => $mikrotik->host ?? 'N/A',
                'port' => $mikrotik->port ?? 'N/A',
                'error' => $e->getMessage()
            ], 503, "Failed to connect to Mikrotik");
            throw $e;
        }
    }
}
