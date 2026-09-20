<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Services\UpdatePayLaterSecretService;

class UpdatePLSecret extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:update-paylater-secret';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $descriptionIn = 'Dispatch job untuk update paylater secret yang jatuh tempo ke Mikrotik';
    protected $descriptionEn = 'Dispatch job to update paylater secret due to Mikrotik';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();
        $currentDateTime = Carbon::now()->setTimezone('Asia/Jakarta');
        $formattedDate = $currentDateTime->format('Y-m-d H:i:s');
        $this->info("Starting " . $this->descriptionEn . " at {$formattedDate}");
        try {
            LogHelper::logInfo(null, [], 200, $this->descriptionEn . " dimulai", $traceId, $timer->lap());
            app(UpdatePayLaterSecretService::class)->updatePayLaterSecret($traceId);
            $dispatchTime = Carbon::now()->format('Y-m-d H:i:s');
            LogHelper::logInfo(null, [], 200, $this->descriptionEn . " selesai", $traceId, $timer->lap());
            $this->info("Successfully " . $this->descriptionEn . " at {$dispatchTime}");
        } catch (\Throwable $e) {
            LogHelper::logError(null, ["error" => $e->getMessage()], 400, "Gagal " . $this->descriptionEn, $traceId, $timer->lap());
            $dispatchTime = Carbon::now()->format('Y-m-d H:i:s');

            $this->info("Failed " . $this->descriptionEn . " at {$dispatchTime}");
        }
        $this->info("Total execution time: {$timer->lap()} seconds");
    }
}
