<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Mikrotik;
use App\Models\Telegram\TelegramGateway;
use App\Models\Telegram\TelegramChatId;
use App\Models\User;
use App\Helpers\LogHelper;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Telegrams\DispatchMessageService;
use Illuminate\Support\Str;
use Exception;
use Throwable;

class UpdateRateLimitProfileSecretJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $routerId;
    protected string $profileName;
    protected string $rateLimit;
    protected ?int $userId;
    protected string $traceId;

    public function __construct(int $routerId, string $profileName, string $rateLimit, ?int $userId = null, ?string $traceId = null)
    {
        $this->routerId = $routerId;
        $this->profileName = $profileName;
        $this->rateLimit = $rateLimit;
        $this->userId = $userId;
        $this->traceId = $traceId ?? (string) Str::uuid();
    }

    public function handle(SecretService $secretService): void
    {
        $router = Mikrotik::find($this->routerId);

        // 1. Log Sebelum Proses Dimulai
        LogHelper::logInfo(null, [
            'profile'    => $this->profileName,
            'rate_limit' => $this->rateLimit,
            'router_id'  => $this->routerId,
            'router'     => $router->name ?? '[not found]',
            'user_id'    => $this->userId,
            'attempt'    => $this->attempts(),
        ], 200, 'Starting Update rate limit PPP profile job', $this->traceId);

        try {
            // Validasi jika router tidak ditemukan di DB
            if (!$router) {
                throw new Exception("Router dengan ID {$this->routerId} tidak ditemukan di database.");
            }

            $secretService->updateRateLimitProfileSecret($router, $this->rateLimit, $this->profileName, $this->traceId);

            // 2. Log Setelah Proses Berhasil Selesai
            LogHelper::logInfo(null, [
                'profile'    => $this->profileName,
                'rate_limit' => $this->rateLimit,
                'router'     => $router->name,
                'attempt'    => $this->attempts(),
            ], 200, 'Successfully updated rate limit PPP profile', $this->traceId);

        } catch (Throwable $e) {
            LogHelper::logWarning(null, [
                'profile'       => $this->profileName,
                'router'        => $router->name ?? '[not found]',
                'attempt'       => $this->attempts(),
                'error_message' => $e->getMessage()
            ], 500, 'Update rate limit PPP profile failed', $this->traceId);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        $router = Mikrotik::find($this->routerId);
        $user = $this->userId ? User::find($this->userId) : null;

        LogHelper::logError(null, [
            'profile'       => $this->profileName,
            'router'        => $router->name ?? '[not found]',
            'error_message' => $exception->getMessage()
        ], 500, "UpdateRateLimitProfileSecretJob failed. Send Message.", $this->traceId);

        $this->notifyTelegram($exception->getMessage(), $router, $user);
    }

    private function notifyTelegram(string $message, ?Mikrotik $router, ?User $user): void
    {
        $telegramGateway = TelegramGateway::where('disabled', false)->first();
        $chat = $user
            ? TelegramChatId::where('user_id', $user->id)->first()
            : TelegramChatId::whereNull('user_id')->first();

        if ($chat && $telegramGateway && $router) {
            $text = "❗ Gagal update rate limit PPP Profile `{$this->profileName}` di router `{$router->name}`. Error: {$message}";

            app(DispatchMessageService::class)
                ->send($chat->chat_id, $text, $this->traceId);
        } else {
            LogHelper::logError(null, [
                'profile'       => $this->profileName,
                'router'        => $router->name ?? '[unknown]',
                'error_message' => 'No chat or gateway found'
            ], 500, "Telegram not sent", $this->traceId);
        }
    }
}