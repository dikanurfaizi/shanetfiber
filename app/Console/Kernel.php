<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Str;
use App\Console\Commands\BillingCheckAndUpdateProfile;
use App\Console\Commands\SendMessages;
use App\Console\Commands\FetchMikrotikInfoCommand;
use App\Jobs\Whatsapp\CloseIdleHumanChatSessionsJob; // 👈 PENTING: Import class Job Anda di sini
use App\Helpers\LogHelper;
use App\Models\Websystem;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //       CreateBillingCron::class,
        //       CloseIdleHumanChatSessionsJob::class, // Jika berbentuk Job, tidak wajib didaftarkan di property $commands ini, cukup di $schedule
        BillingCheckAndUpdateProfile::class,
        SendMessages::class,
        FetchMikrotikInfoCommand::class
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $defaultTimezone = 'Asia/Jakarta';

        // === Scheduler Heartbeat ===
        $schedule->command('system:scheduler-heartbeat')
            ->hourly()
            ->timezone($defaultTimezone)
            ->runInBackground();

        /*
        |--------------------------------------------------------------------------
        | 1. AUTO CREATE BILLING
        |--------------------------------------------------------------------------
        | Dijalankan hanya pada tanggal yang ditentukan di konfigurasi (billing_auto_day)
        | jika auto_generate aktif.
        */
        $schedule->command('create_billing:cron')
            ->dailyAt('00:05')
            ->timezone($defaultTimezone)
            ->when(function () {
                try {
                    return cache()->remember(env('APP_ENV') . ':scheduler:websystem-check', now()->addMinutes(5), function () {
                        $cfg = Websystem::first();
                        return $cfg && $cfg->billing_auto_generate && now()->day == $cfg->billing_auto_day;
                    });
                } catch (\Throwable $e) {
                    $traceId = uniqid('scheduler_') . Str::random(5);
                    LogHelper::logWarning(
                        null,
                        ['error' => ['code' => $e->getCode(), 'message' => $e->getMessage()]],
                        500,
                        '[Scheduler] Skipped create_billing:cron',
                        $traceId
                    );
                    return false;
                }
            })
            ->onFailure(fn() => LogHelper::logError(null, ['time' => now()], 500, '[Scheduler] create_billing:cron failed'))
            ->onSuccess(fn() => LogHelper::logInfo(null, ['time' => now()], 200, '[Scheduler] create_billing:cron finished successfully'))
            ->withoutOverlapping(30)
            ->appendOutputTo(storage_path('logs/scheduler-create_billing.log'))
            ->onOneServer();

        /*
        |--------------------------------------------------------------------------
        | 2. UPDATE PAYLATER SECRET
        |--------------------------------------------------------------------------
        | Dijalankan setiap hari pukul 00:15 untuk sinkronisasi secret ke Mikrotik.
        | Tidak tergantung pada tanggal tertentu, hanya validasi logikanya internal.
        */
        // $schedule->command('billing:update-paylater-secret')
        //     ->dailyAt('00:15')
        //     ->timezone($defaultTimezone)
        //     ->withoutOverlapping(30)
        //     ->appendOutputTo(storage_path('logs/scheduler-update_paylater_secret.log'))
        //     ->evenInMaintenanceMode();

        /*
        |--------------------------------------------------------------------------
        | 3. INITIAL PAYMENT BROADCAST
        |--------------------------------------------------------------------------
        | Dijalankan harian hanya pada tanggal 1 pukul 08:00 (WIB).
        | Satu kali jalan langsung mengirimkan semua data.
        */
        $schedule->command('initial-payment:messages-send')
            ->dailyAt('08:00')
            ->timezone($defaultTimezone)
            ->when(fn() => now()->day === 1)
            ->onFailure(fn() => LogHelper::logError(null, ['time' => now()], 500, '[Scheduler] initial-payment:messages-send failed'))
            ->onSuccess(fn() => LogHelper::logInfo(null, ['time' => now()], 200, '[Scheduler] initial-payment:messages-send finished successfully'))
            ->withoutOverlapping(30)
            ->appendOutputTo(storage_path('logs/scheduler-initial-payment.log'));

        /*
        |--------------------------------------------------------------------------
        | 4. REMINDER WHATSAPP MESSAGES
        |--------------------------------------------------------------------------
        | Biarkan berjalan setiap 30 menit secara global. 
        | Validasi tanggal & jam diserahkan sepenuhnya ke DB via Command.
        | */
        $schedule->command('reminder:messages-send')
            ->everyThirtyMinutes()
            ->timezone($defaultTimezone)
            ->withoutOverlapping(10) // Cukup tempelkan lock 10-30 menit agar tidak tabrakan
            ->appendOutputTo(storage_path('logs/scheduler-reminder.log'));

        /*
        |--------------------------------------------------------------------------
        | 5. OVERDUE REMINDER WHATSAPP MESSAGES
        |--------------------------------------------------------------------------
        | Dijalankan setiap 15 menit sepanjang waktu.
        |
        */
        $schedule->command('reminder:overdue-messages-send')
            ->everyFifteenMinutes()
            ->timezone($defaultTimezone)
            ->onFailure(fn() => LogHelper::logError(null, ['time' => now()], 500, '[Scheduler] reminder:overdue-messages-send failed'))
            ->onSuccess(fn() => LogHelper::logInfo(null, ['time' => now()], 200, '[Scheduler] reminder:overdue-messages-send finished successfully'))
            ->withoutOverlapping(30)
            ->appendOutputTo(storage_path('logs/scheduler-reminder.log'));

        /*
        |--------------------------------------------------------------------------
        | 6. ARCHIVE LARAVEL LOGS
        |--------------------------------------------------------------------------
        | Berjalan setiap menit untuk memantau jika ukuran log mendekati 3MB.
        | Otomatis mengarsip terjadwal harian tepat pada pukul 23:59.
        | */
        $schedule->command('logs:rotate')
            ->everyMinute()
            ->timezone($defaultTimezone)
            ->withoutOverlapping()
            ->onOneServer()
            ->evenInMaintenanceMode()
            ->appendOutputTo(storage_path('logs/scheduler-rotate.log'));

        /*
        |--------------------------------------------------------------------------
        | 7. CLEANUP ARCHIVED LOGS
        |--------------------------------------------------------------------------
        | Berjalan setiap hari pada pukul 23:59 untuk menghapus berkas arsip 
        | yang umurnya sudah melewati batas retensi (15 hari).
        | */
        $schedule->command('logs:cleanup')
            ->dailyAt('23:59')
            ->timezone($defaultTimezone)
            ->withoutOverlapping()
            ->onOneServer()
            ->evenInMaintenanceMode()
            ->appendOutputTo(storage_path('logs/scheduler-cleanup.log'));

        /*
        |--------------------------------------------------------------------------
        | 8. DELETE OLD MESSAGE LOGS
        |--------------------------------------------------------------------------
        | Dijalankan setiap hari pada pukul 00:30,
        | Jika kondisi tidak terpenuhi, dilewati (tidak error).
        */
        $schedule->command('logs:delete-old-messages')
            ->dailyAt('00:30')
            ->timezone($defaultTimezone)
            ->withoutOverlapping(30)
            ->onOneServer()
            ->onFailure(fn() => LogHelper::logError(null, ['time' => now()], 500, '[Scheduler] logs:delete-old-messages failed'))
            ->onSuccess(fn() => LogHelper::logInfo(null, ['time' => now()], 200, '[Scheduler] logs:delete-old-messages finished successfully'))
            ->appendOutputTo(storage_path('logs/scheduler-delete_old_messages.log'));

        /*
        |--------------------------------------------------------------------------
        | 9. FETCH MIKROTIK INFO
        |--------------------------------------------------------------------------
        | Dijalankan setiap 30 menit antara jam 06:00 - 22:00 (WIB).
        */
        $schedule->command('network:fetch-mikrotik-info')
            ->everyThirtyMinutes()
            ->timezone($defaultTimezone)
            ->when(fn() => now()->hour >= 6 && now()->hour < 22)
            ->onFailure(fn() => LogHelper::logError(null, ['time' => now()], 500, '[Scheduler] network:fetch-mikrotik-info failed'))
            ->onSuccess(fn() => LogHelper::logInfo(null, ['time' => now()], 200, '[Scheduler] network:fetch-mikrotik-info finished successfully'))
            ->withoutOverlapping(45)
            ->appendOutputTo(storage_path('logs/scheduler-fetch_mikrotik_info.log'));

        /*
        |--------------------------------------------------------------------------
        | 10. SCAN OLTS
        |--------------------------------------------------------------------------
        | Dijalankan setiap 15 menit antara jam 06:00 - 22:00 (WIB).
        */
        $schedule->command('network:scan-olts')
            ->everyFifteenMinutes()
            ->timezone($defaultTimezone)
            ->when(fn() => now()->hour >= 6 && now()->hour < 22)
            ->onFailure(fn() => LogHelper::logError(null, ['time' => now()], 500, '[Scheduler] network:scan-olts failed'))
            ->onSuccess(fn() => LogHelper::logInfo(null, ['time' => now()], 200, '[Scheduler] network:scan-olts finished successfully'))
            ->withoutOverlapping(45)
            ->appendOutputTo(storage_path('logs/scheduler-scan_olts.log'));

        /*
        |--------------------------------------------------------------------------
        | 11. APPLY SCHEDULED SUBSCRIPTION CHANGES
        |--------------------------------------------------------------------------
        | Dijalankan setiap hari pada pukul 01:00,
        */
        // $schedule->command('subscriptions:apply-scheduled')
        //     ->dailyAt('00:01')
        //     ->withoutOverlapping()
        //     ->onOneServer()
        //     ->appendOutputTo(storage_path('logs/scheduler-subscriptions.log'));

        /*
        |--------------------------------------------------------------------------
        | 12. PAYMENT CHANNEL UPDATE BROADCAST
        |--------------------------------------------------------------------------
        | Dijalankan setiap 5 jam dari tanggal 1 sampai 15 Juni 2026.
        | Output otomatis di-append ke custom log file.
        | */
        $schedule->command('reminder:payment-channel-update-send')
            ->cron('0 */5 * * *')
            ->when(function () {
                return now()->between('2026-06-01 00:00:00', '2026-06-25 23:59:59');
            })
            ->onFailure(fn() => LogHelper::logError(null, ['time' => now()], 500, '[Scheduler] reminder:payment-channel-update-send failed'))
            ->onSuccess(fn() => LogHelper::logInfo(null, ['time' => now()], 200, '[Scheduler] reminder:payment-channel-update-send finished successfully'))
            ->withoutOverlapping(10)
            ->appendOutputTo(storage_path('logs/scheduler-payment-channel.log'));

        /*
        |--------------------------------------------------------------------------
        | 13. AUTOMATICALLY CLOSE IDLE HUMAN CHAT SESSIONS (UBAH KE COMMAND)
        |--------------------------------------------------------------------------
        | Mengecek dan menutup sesi obrolan dengan admin manusia yang tidak aktif 
        | selama 15 menit secara real-time setiap menit.
        | */
        $schedule->command('whatsapp:close-idle-sessions') // 👈 Menggunakan signature command baru Anda
            ->everyMinute()
            ->timezone($defaultTimezone)
            ->withoutOverlapping(5)
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/scheduler-whatsapp-timeout.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
