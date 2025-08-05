<?php

namespace App\Jobs;

use App\Models\Mikrotik;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Routerboard\Ppp\SecretService;
use App\Helpers\LogHelper;

class EnablePppSecretJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private SecretService $secretService;

    public function __construct(
        private Mikrotik $mikrotik,
        private string $username,
        private int $customerId

    ) {
        $this->secretService = app(SecretService::class);
    }

    public function handle(): void
    {
        try {
            // 1. Enable the PPP secret
            $this->secretService->enableSecret($this->mikrotik, $this->username);

            // 2. Update customer status in database
            Customer::where('id', $this->customerId)->update(['disabled' => 'false']);

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $this->customerId,
                    'username' => $this->username,
                    'router_id' => $this->mikrotik->id
                ],
                200,
                'Successfully enabled PPP secret',
                config('app.trace_id')
            );
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $this->customerId,
                    'router_id' => $this->mikrotik->id
                ],
                500,
                'Failed to enable PPP secret',
                config('app.trace_id')
            );

            $this->release(30); // Retry after 30 seconds if fails
        }
    }
}
