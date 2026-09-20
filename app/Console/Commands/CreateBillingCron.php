<?php

namespace App\Console\Commands;

use App\Helpers\ExecutionTimer;
use App\Jobs\Billing\CreateInitialBillingJob;
use App\Models\Websystem;
use App\Models\Billings\BillingPeriode;
use App\Helpers\LogHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateBillingCron extends Command
{
    protected $signature   = 'create_billing:cron';
    protected $description = 'Automatically create billing for new period';

    public function handle(): int
    {
        $traceId   = (string) Str::uuid();
        $timer = new ExecutionTimer();
        $startedAt = now('Asia/Jakarta');

        // ===== CONSOLE (human) =====
        $this->info("▶ CreateBillingCron started at {$startedAt->toDateTimeString()}");

        // ===== LOG (system) =====
        LogHelper::logInfo(
            null,
            [
                'started_at' => $startedAt->toDateTimeString(),
            ],
            200,
            'BILLING_CRON.START',
            $traceId,
            $timer->lap()
        );

        try {
            $currentPeriod  = $startedAt->format('F Y');
            $lastPeriodName = BillingPeriode::latest('created_at')->value('name');
            $cfg            = Websystem::firstOrFail();

            // ===== Skip: period already exists =====
            if ($currentPeriod === $lastPeriodName) {

                $this->warn("⏭ Billing period '{$currentPeriod}' already exists. Skipping.");

                LogHelper::logInfo(
                    null,
                    [
                        'current_period' => $currentPeriod,
                        'last_period'    => $lastPeriodName,
                    ],
                    202,
                    'BILLING_CRON.SKIP.PERIOD_EXISTS',
                    $traceId,
                    $timer->lap()
                );

                return Command::SUCCESS;
            }

            // ===== Skip: auto-generate disabled =====
            if (!$cfg->billing_auto_generate) {

                $this->warn("⏭ Billing auto-generation is disabled. Skipping.");

                LogHelper::logInfo(
                    null,
                    [
                        'current_period' => $currentPeriod,
                    ],
                    202,
                    'BILLING_CRON.SKIP.AUTO_GENERATE_DISABLED',
                    $traceId,
                    $timer->lap()
                );

                return Command::SUCCESS;
            }

            // ===== Dispatch Job =====
            CreateInitialBillingJob::dispatch($currentPeriod, null, $traceId)
                ->onQueue('default');

            $this->info("✅ Billing job for '{$currentPeriod}' dispatched.");

            LogHelper::logInfo(
                null,
                [
                    'billing_period' => $currentPeriod,
                    'queue'          => 'default',
                ],
                200,
                'BILLING_CRON.JOB_DISPATCHED',
                $traceId,
                $timer->lap()
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {

            $this->error("❌ CreateBillingCron failed: {$e->getMessage()}");

            LogHelper::logError(
                null,
                [
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                ],
                500,
                'BILLING_CRON.FAILED',
                $traceId,
                $timer->lap()
            );

            return Command::FAILURE;
        }
    }
}
