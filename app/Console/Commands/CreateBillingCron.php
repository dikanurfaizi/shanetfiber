<?php

namespace App\Console\Commands;

use App\Models\Websystem;
use App\Models\Billing\BillingPeriode;
use App\Helpers\LogHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CreateBillingCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_billing:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically create billing if conditions are met';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $traceId = (string) Str::uuid();

        $startTime = microtime(true);
        $currentDateTime = Carbon::now()->setTimezone('Asia/Jakarta');
        $formattedDate = $currentDateTime->format('Y-m-d H:i:s');
        $this->info("Starting create billing periode at {$formattedDate}");
        try {
            $periodeNow = Carbon::now()->format('F Y');
            $billingPeriodeName = BillingPeriode::latest('created_at')->value('name');

            // Cek apakah billing periode bulan ini sudah ada
            if ($periodeNow == $billingPeriodeName) {
                LogHelper::logInfo(null, [
                    "periode-now" => $periodeNow,
                    "last-periode" => $billingPeriodeName
                ], 202, "Billing Periode '{$periodeNow}' sudah tersedia. Proses dihentikan.", $traceId);
                return;
            }

            // Ambil konfigurasi dari Websystem
            $websystem = Websystem::first();
            if (!$websystem) {
                LogHelper::logError(null, [], 500, "Konfigurasi Websystem tidak ditemukan");
                return;
            }
            $billingAutoGenerate = $websystem?->billing_auto_generate;
            $billingAutoDay = $websystem?->billing_auto_day;

            // Jika tidak aktif, log dan berhenti
            if (!$billingAutoGenerate) {
                LogHelper::logInfo(null, [], 202, "Auto generate billing tidak aktif di konfigurasi", $traceId);
                return;
            }

            // Cek apakah hari ini sama dengan billing_auto_day
            if ((int) now()->day !== (int) $billingAutoDay) {
                LogHelper::logInfo(null, [], 202, "Hari ini (" . now()->day . ") bukan tanggal yang dijadwalkan (" . $billingAutoDay . ")", $traceId);
                return;
            }

            // Jalankan controller langsung
            LogHelper::logInfo(null, [], 200, "Mulai eksekusi auto generate billing untuk periode '{$periodeNow}'", $traceId);

            // Jalankan controller dengan request palsu
            request()->merge(['name' => $periodeNow]);
            app()->call('App\Http\Controllers\BillingController@store', [
                'request' => request()
            ]);
            LogHelper::logInfo(null, [], 200, "Auto billing untuk periode '{$periodeNow}' berhasil dijalankan.", $traceId);

            $dispatchTime = Carbon::now()->format('Y-m-d H:i:s');

            $this->info("Successfully create billing periode at {$dispatchTime}");
        } catch (\Throwable $e) {
            LogHelper::logError(null, ["error" => $e->getMessage()], 400, "Gagal membuat billing otomatis", $traceId);
            $dispatchTime = Carbon::now()->format('Y-m-d H:i:s');

            $this->info("Failed create billing periode at {$dispatchTime}");
        }
        $executionTime = round(microtime(true) - $startTime, 2);
        $this->info("Total execution time: {$executionTime} seconds");
    }
}
