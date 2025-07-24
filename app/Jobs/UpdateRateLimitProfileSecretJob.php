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
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\TelegramMessageService;
use Illuminate\Support\Str;
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

        try {
            $secretService->updateRateLimitProfileSecret($router, $this->rateLimit, $this->profileName);
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

            app(TelegramMessageService::class)
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
