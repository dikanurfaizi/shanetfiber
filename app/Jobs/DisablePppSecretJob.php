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

class DisablePppSecretJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private SecretService $secretService;
    private string $traceId;

    public function __construct(private Mikrotik $mikrotik, private string $username, private int $userId, string $traceId)
    {
        $this->secretService = app(SecretService::class);
        $this->traceId = $traceId;
    }

    public function handle(): void
    {
        try {
            // 1. Hapus session aktif
            $this->secretService->deleteActiveSecret($this->mikrotik, $this->username, $this->traceId);

            // 2. Nonaktifkan secret
            $this->secretService->disableSecret($this->mikrotik, $this->username, $this->traceId);

            // 3. Update status di database
            Customer::where('id', $this->userId)->update(['disabled' => 'true']);

            LogHelper::logInfo(
                null,
                [
                    'username' => $this->username,
                    'router_id' => $this->mikrotik->id
                ],
                200,
                'Successfully disabled PPP secret',
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
                    'username' => $this->username,
                    'router_id' => $this->mikrotik->id
                ],
                500,
                'Failed to disable PPP secret',
                config('app.trace_id')
            );

            // Optionally retry the job
            $this->release(30); // Retry after 30 seconds
        }
    }
}
