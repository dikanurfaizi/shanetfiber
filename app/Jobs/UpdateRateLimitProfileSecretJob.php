<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Mikrotik;
use App\Models\TelegramGateway\TelegramGateway;
use App\Models\TelegramGateway\TelegramChatId;
use App\Models\User;
use App\Helpers\LogHelper;
use Exception;
use RouterOS\Query;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\StreamException;
use App\Services\TelegramMessageService;
use Illuminate\Support\Str;

class UpdateRateLimitProfileSecretJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $router;
    protected $profileName;
    protected $rateLimit;
    protected $user;

    public $tries = 5;
    public $timeout = 60;
    public $maxExceptions = 3;

    public function __construct(Mikrotik $router, string $profileName, string $rateLimit, ?User $user = null)
    {
        $this->router = $router;
        $this->profileName = $profileName;
        $this->rateLimit = $rateLimit;
        $this->user = $user;
    }

    public function handle()
    {
        $traceId = (string) Str::uuid();
        $maxLocalRetries = 3;
        $attempt = 0;
        $timeToSendWhatsapp = $maxLocalRetries - 1;

        while ($attempt < $maxLocalRetries) {
            try {
                $this->updateRateLimitProfileSecret(
                    $this->router,
                    $this->rateLimit,
                    $this->profileName,
                    $this->user,
                    $traceId
                );

                return; // Berhasil – keluar dari job
            } catch (StreamException $e) {          // ⬅️  baru
                LogHelper::logWarning(null, [
                    'attempt'       => $attempt,
                    'profile'       => $this->profileName,
                    'router'        => $this->router->name,
                    'error_message' => 'Stream timed out: ' . $e->getMessage()
                ], 504, "Stream timeout while updating rate limit");

                if ($attempt >= $timeToSendWhatsapp) {
                    $this->failed($e->getMessage(), $traceId, $this->user);   // kirim Telegram setelah 3× gagal
                    throw $e;                           // biar supervisor queue men‑mark job failed
                }

                $attempt++;
                sleep(10); // jeda sebelum retry
            } catch (Exception $e) {                  // existing generic catch
                LogHelper::logWarning(null, [
                    'attempt'       => $attempt,
                    'profile'       => $this->profileName,
                    'router'        => $this->router->name,
                    'error_message' => $e->getMessage()
                ], 500, "Failed to update rate limit");

                if ($attempt >= $timeToSendWhatsapp) {
                    $this->failed($e->getMessage(), $traceId, $this->user);
                    throw $e;
                }

                $attempt++;
                sleep(10);
            }
        }
    }

    public function failed(string $message, $traceId, $user)
    {
        LogHelper::logError(null, [
            'profile' => $this->profileName,
            'router' => $this->router->name,
            'error_message' => $message
        ], 500, "UpdateRateLimitProfileSecretJob failed");

        $telegramGateway = TelegramGateway::where('disabled', false)->get();
        $userId = $user?->id ?? '1';
        $chat = TelegramChatId::where('user_id', $userId)->get();

        if ($chat) {
            $message = "❗ Gagal update rate limit PPP Profile `{$this->profileName}` di router `{$this->router->name}`. Error: {$message}";

            if ($telegramGateway && $telegramGateway->disabled) {
                $telegramMessageService = app(TelegramMessageService::class);
                $telegramMessageService->send($chat->chat_id, $message, $traceId);
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

    private function updateRateLimitProfileSecret($mikrotik, $rateLimit, $profileName, $user, $traceId)
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
            ], 404, "PPP Profile not found during rate limit update", $traceId);

            $this->failed("Profile '{$profileName}' not found in Mikrotik '{$mikrotik->name}' with host '{$mikrotik->host}'", $traceId, $user);
            throw new Exception("Profile '{$profileName}' not found in Mikrotik '{$mikrotik->name}' with host '{$mikrotik->host}'");
        }

        $updateQuery = (new Query('/ppp/profile/set'))
            ->equal('.id', $targetProfile['.id'])
            ->equal('rate-limit', $rateLimit);

        return $client->query($updateQuery)->read();
    }

    // private function updateRateLimitProfileSecret($mikrotik, $rateLimit, $profileName, $user, $traceId)
    // {
    //     // Dummy simulasi validasi: misalnya profileName harus tidak kosong
    //     if (empty($profileName)) {
    //         LogHelper::logWarning(null, [
    //             'host' => $mikrotik->host ?? 'Unknown Host',
    //             'name' => $mikrotik->name,
    //             'profile_name' => $profileName
    //         ], 404, "PPP Profile not found during rate limit update (dummy)");

    //         $this->failed("Profile '{$profileName}' not found in Mikrotik '{$mikrotik->name}' with host '{$mikrotik->host}'", $traceId, $user);
    //         throw new Exception("Profile '{$profileName}' not found in Mikrotik '{$mikrotik->name}' with host '{$mikrotik->host}'");
    //     }

    //     // Simulasi dummy log info (tanpa eksekusi query)
    //     LogHelper::logInfo(null, [
    //         'host' => $mikrotik->host,
    //         'name' => $mikrotik->name,
    //         'profile_name' => $profileName,
    //         'rate_limit' => $rateLimit,
    //         'user' => $user?->name ?? 'unknown',
    //         'user_id' => $user?->id ?? 'unknown',
    //         'trace_id' => $traceId
    //     ], 200, "Dummy success update rate limit PPP profile");

    //     // Return dummy response seperti hasil asli
    //     return [
    //         'status' => 'success',
    //         'message' => "Rate limit '{$rateLimit}' applied to profile '{$profileName}' on router '{$mikrotik->name}'"
    //     ];
    // }

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
