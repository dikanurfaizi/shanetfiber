<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Mikrotik;
use App\Services\Routerboard\PPP\SecretService;
use Exception;
use App\Helpers\LogHelper;
use App\Models\WhatsAppGateway;
use App\Models\Websystem;
use App\Services\WhatsappMessageService;

class UpdateRateLimitProfileSecretJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $router;
    protected $profileName;
    protected $rateLimit;

    public $tries = 5;
    public $timeout = 60; // boleh ditambahin sedikit dari 30 detik
    public $maxExceptions = 3; // kalau terjadi exception terus menerus, stop di 3

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $router, string $profileName, string $rateLimit)
    {
        $this->router = $router;
        $this->profileName = $profileName;
        $this->rateLimit = $rateLimit;
    }

    /**
     * Execute the job.
     */
    public function handle(SecretService $secretService)
    {
        $maxLocalRetries = 3;
        $attempt = 0;

        while ($attempt < $maxLocalRetries) {
            try {
                $secretService->updateRateLimitProfileSecret(
                    $this->router,
                    $this->rateLimit,
                    $this->profileName
                );

                // Jika berhasil, keluar dari loop
                return;
            } catch (Exception $e) {
                $attempt++;

                // Log lokal supaya tahu pernah error
                LogHelper::logWarning(null, ['attempt' => $attempt, 'profile' => $this->profileName, 'router' => $this->router->name, 'error_message' => $e->getMessage()], 500, "Failed to update rate limit");

                if ($attempt >= $maxLocalRetries) {
                    // Kalau gagal setelah semua percobaan manual, lempar lagi error
                    $this->failed($e);
                    throw $e;
                }

                // Tunggu sebelum retry lagi
                sleep(10);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception)
    {
        // Bisa dikembangkan kirim alert ke Telegram, Slack, dsb
        LogHelper::logError(null, ['profile' => $this->profileName, 'router' => $this->router->name, 'error_message' => $exception->getMessage()], 500, "UpdateRateLimitProfileSecretJob failed");
        $gateway = WhatsappGateway::where('slug', 'gateway')->first();
        $websystem = Websystem::query()->first();
        if (!$websystem) {
            // Jika tidak ada data di Websystem, log error dan keluar
            LogHelper::logError(null, ['profile' => $this->profileName, 'router' => $this->router->name, 'error_message' => 'Websystem data not found'], 500, "Websystem data missing");
            return;
        }
        $operatorPhone = $websystem->phone ?? null;
        $customerName = $websystem->title ?? 'Admin';
        if ($operatorPhone) {
            // Normalisasi nomor HP
            $operatorPhone = ltrim($operatorPhone); // buang spasi di depan
            if (!preg_match('/^(\+?62|08|8)/', $operatorPhone)) {
                LogHelper::logError(null, ['profile' => $this->profileName, 'router' => $this->router->name, 'error_message' => 'Invalid phone number format'], 500, "Invalid operator phone number");
                return;
            }
            $message = "❗ Gagal update rate limit PPP Profile `{$this->profileName}` di router `{$this->router->name}`. Error: {$exception->getMessage()}";

            if ($gateway && $gateway->disabled === 'false' && $operatorPhone) {
                dispatch(new SendWhatsappSingleJob($customerName, $operatorPhone, $gateway, $message));
            }
        }
    }

    /**
     * Define backoff time between retries.
     */
    public function backoff()
    {
        return [30, 60, 120];
    }
}
