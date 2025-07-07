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
use RouterOS\Query;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\StreamException;
use App\Services\TelegramMessageService;
use Illuminate\Support\Str;
use Throwable;

class UpdateRateLimitProfileSecretJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $router;
    protected $profileName;
    protected $rateLimit;
    protected $user;
    protected string $traceId;

    public function __construct(Mikrotik $router, string $profileName, string $rateLimit, ?User $user = null)
    {
        $this->router = $router;
        $this->profileName = $profileName;
        $this->rateLimit = $rateLimit;
        $this->user = $user;
        $this->traceId = (string) Str::uuid();
    }

    public function handle(): void
    {
        try {
            $this->updateRateLimitProfileSecret(
                $this->router,
                $this->rateLimit,
                $this->profileName,
                $this->user
            );
        } catch (Throwable $e) {
            LogHelper::logWarning(null, [
                'profile'       => $this->profileName,
                'router'        => $this->router->name,
                'attempt'       => $this->attempts(),
                'error_message' => $e->getMessage()
            ], 500, 'Update rate limit PPP profile failed', $this->traceId);

            throw $e; // Laravel akan retry & panggil failed()
        }
    }

    public function failed(Throwable $exception): void
    {
        LogHelper::logError(null, [
            'profile'       => $this->profileName,
            'router'        => $this->router->name,
            'error_message' => $exception->getMessage()
        ], 500, "UpdateRateLimitProfileSecretJob failed", $this->traceId);

        $this->notifyTelegram($exception->getMessage());
    }

    private function notifyTelegram(string $message): void
    {
        $telegramGateway = TelegramGateway::where('disabled', false)->first();
        $chat = TelegramChatId::where('user_id', $this->user?->id ?? 1)->first();

        if ($chat && $telegramGateway) {
            $text = "❗ Gagal update rate limit PPP Profile `{$this->profileName}` di router `{$this->router->name}`. Error: {$message}";

            app(TelegramMessageService::class)
                ->send($chat->chat_id, $text, $this->traceId);
        } else {
            LogHelper::logError(null, [
                'profile'       => $this->profileName,
                'router'        => $this->router->name,
                'error_message' => 'No chat or gateway found'
            ], 500, "Telegram not sent", $this->traceId);
        }
    }

    private function updateRateLimitProfileSecret($mikrotik, $rateLimit, $profileName, $user)
    {
        $client = $this->getMikrotik($mikrotik);

        // Ambil semua profile yang ada
        $profiles      = $client->query(new Query('/ppp/profile/print'))->read();
        $targetProfile = collect($profiles)->firstWhere('name', $profileName);

        // ─────────────────────────────────────────
        //  CASE 1: Profile TIDAK ada  →  INSERT
        // ─────────────────────────────────────────
        if (! $targetProfile) {
            $addQuery = (new Query('/ppp/profile/add'))
                ->equal('name', $profileName)
                ->equal('rate-limit', $rateLimit);

            $result = $client->query($addQuery)->read();

            LogHelper::logInfo(null, [
                'router'       => $mikrotik->name,
                'profile_name' => $profileName,
                'rate_limit'   => $rateLimit,
                'action'       => 'create'
            ], 201, 'PPP profile created', $this->traceId);

            return $result;            // selesai: profile baru dibuat
        }

        // ─────────────────────────────────────────
        //  CASE 2: Profile ADA  →  UPDATE
        // ─────────────────────────────────────────
        $updateQuery = (new Query('/ppp/profile/set'))
            ->equal('.id', $targetProfile['.id'])
            ->equal('rate-limit', $rateLimit);

        $result = $client->query($updateQuery)->read();

        LogHelper::logInfo(null, [
            'router'       => $mikrotik->name,
            'profile_name' => $profileName,
            'rate_limit'   => $rateLimit,
            'action'       => 'update'
        ], 200, 'PPP profile updated', $this->traceId);

        return $result;
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
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'host'  => $mikrotik->host,
                'port'  => $mikrotik->port,
                'error' => $e->getMessage()
            ], 503, "Failed to connect to Mikrotik", $this->traceId);

            throw $e;
        }
    }
}
