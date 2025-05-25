<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\LogHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Jobs\MigrateCustomerToRouter;
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
        $startTime = microtime(true);
        $currentDateTime = Carbon::now()->setTimezone('Asia/Jakarta');
        $formattedDate = $currentDateTime->format('Y-m-d H:i:s');
        $this->info("Starting " . $this->descriptionEn . " at {$formattedDate}");
        try {
            LogHelper::logInfo(null, [], 200, $this->descriptionEn . " dimulai", $traceId);
            $mikrotik = Mikrotik::findOrFail(1);
            if ($mikrotik) {
                dispatch(new MigrateCustomerToRouter($mikrotik, $mikrotik))->onQueue('default');
            }
            $dispatchTime = Carbon::now()->format('Y-m-d H:i:s');
            LogHelper::logInfo(null, [], 200, $this->descriptionEn . " selesai", $traceId);
            $this->info("Successfully " . $this->descriptionEn . " at {$dispatchTime}");
        } catch (\Throwable $e) {
            LogHelper::logError(null, ["error" => $e->getMessage()], 400, "Gagal " . $this->descriptionEn, $traceId);
            $dispatchTime = Carbon::now()->format('Y-m-d H:i:s');

            $this->info("Failed " . $this->descriptionEn . " at {$dispatchTime}");
        }
        $executionTime = round(microtime(true) - $startTime, 2);
        $this->info("Total execution time: {$executionTime} seconds");
    }
}
