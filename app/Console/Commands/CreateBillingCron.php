<?php

namespace App\Console\Commands;

use App\Jobs\Billing\CreateInitialBillingJob;
use App\Models\Websystem;
use App\Models\Billing\BillingPeriode;
use App\Helpers\LogHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateBillingCron extends Command
{
    protected $signature   = 'create_billing:cron';
    protected $description = 'Automatically create billing if conditions are met';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $traceId   = (string) Str::uuid();
        $startedAt = now('Asia/Jakarta');
        $this->info("Starting CreateBillingCron at {$startedAt->toDateTimeString()}");

        try {
            $currentPeriod  = $startedAt->format('F Y');
            $lastPeriodName = BillingPeriode::latest('created_at')->value('name');
            $cfg            = Websystem::firstOrFail();

            if ($currentPeriod === $lastPeriodName) {
                LogHelper::logInfo(null, [], 202, "Billing period '{$currentPeriod}' already exists.", $traceId);
                return;
            }

            if (!$cfg->billing_auto_generate) {
                LogHelper::logInfo(null, [], 202, "Billing auto-generation is disabled.", $traceId);
                return;
            }

            // ===== CALL JOB =====
            CreateInitialBillingJob::dispatch($currentPeriod, null)->onQueue('default');
            $this->info("✅ Billing for {$currentPeriod} has been successfully dispatched.");
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                ['error' => $e->getMessage()],
                500,
                'CreateBillingCron execution failed.',
                $traceId
            );
            $this->error("❌ Failed: {$e->getMessage()}");
        }
    }
}
