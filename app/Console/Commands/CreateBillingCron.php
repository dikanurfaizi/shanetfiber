<?php

namespace App\Console\Commands;

use App\Services\CreateBillingService;
use App\Models\Websystem;
use App\Models\Billing\BillingPeriode;
use App\Helpers\LogHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateBillingCron extends Command
{
    protected $signature   = 'create_billing:cron';
    protected $description = 'Automatically create billing if conditions are met';

    public function __construct(
        protected CreateBillingService $billingService   // ← DI service
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $traceId     = (string) Str::uuid();
        $startedAt   = now('Asia/Jakarta');
        $this->info("Starting CreateBillingCron at {$startedAt->toDateTimeString()}");

        try {
            $periodeNow         = $startedAt->format('F Y');
            $lastPeriodeName    = BillingPeriode::latest('created_at')->value('name');

            if ($periodeNow === $lastPeriodeName) {
                LogHelper::logInfo(
                    null,
                    [],
                    202,
                    "Billing Periode '{$periodeNow}' sudah tersedia. Proses dihentikan.",
                    $traceId
                );
                return;
            }

            $cfg = Websystem::firstOrFail();

            if (!$cfg->billing_auto_generate) {
                LogHelper::logInfo(
                    null,
                    [],
                    202,
                    "Auto‑generate billing tidak aktif di konfigurasi.",
                    $traceId
                );
                return;
            }

            if ((int) $startedAt->day !== (int) $cfg->billing_auto_day) {
                LogHelper::logInfo(
                    null,
                    [],
                    202,
                    "Hari ini ({$startedAt->day}) bukan tanggal yang dijadwalkan ({$cfg->billing_auto_day}).",
                    $traceId
                );
                return;
            }

            // ========= CALL SERVICE =========
            $this->billingService->store($periodeNow);

            $this->info("✅ Billing {$periodeNow} selesai diproses.");
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                ['error' => $e->getMessage()],
                500,
                'CreateBillingCron gagal dieksekusi.',
                $traceId
            );
            $this->error("❌ Gagal: {$e->getMessage()}");
        }
    }
}
