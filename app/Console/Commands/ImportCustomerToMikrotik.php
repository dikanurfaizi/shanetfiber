<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Jobs\ExportMikrotik\MigrateCustomerToRouter;
use App\Models\Mikrotik;

class ImportCustomerToMikrotik extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:migrate-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $descriptionIn = 'Dispatch job untuk migrasi user ke Mikrotik Tertentu';
    protected $descriptionEn = 'Dispatch job for user migration to a specific Mikrotik';

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
            $mikrotik = Mikrotik::findOrFail(1);
            if ($mikrotik) {
                dispatch(new MigrateCustomerToRouter($mikrotik, $mikrotik, $traceId))->onQueue('default');
            }
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
