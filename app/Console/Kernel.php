<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Str;
use App\Console\Commands\BillingCheckAndUpdateProfile;
use App\Console\Commands\SendMessages;
use App\Console\Commands\FetchMikrotikInfoCommand;
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
        //      CreateBillingCron::class,
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
        | Dijalankan setiap 15 menit hanya pada tanggal 1, 2, dan 3,
        | serta hanya antara jam 08:00 - 22:00 (WIB).
        | Jika kondisi tidak terpenuhi, dilewati (tidak error).
        */
        // $schedule->command('initial-payment:messages-send')
        //     ->everyFifteenMinutes()
        //     ->timezone($defaultTimezone)
        //     ->when(fn() => in_array(now()->day, [1, 2, 3]) && now()->hour >= 8 && now()->hour < 22)
        //     ->onFailure(fn() => LogHelper::logError(null, ['time' => now()], 500, '[Scheduler] initial-payment:messages-send failed'))
        //     ->onSuccess(fn() => LogHelper::logInfo(null, ['time' => now()], 200, '[Scheduler] initial-payment:messages-send finished successfully'))
        //     ->withoutOverlapping(30)
        //     ->appendOutputTo(storage_path('logs/scheduler-initial-payment.log'));

        /*
        |--------------------------------------------------------------------------
        | 4. REMINDER WHATSAPP MESSAGES
        |--------------------------------------------------------------------------
        | Biarkan berjalan setiap 15 menit secara global. 
        | Validasi tanggal & jam diserahkan sepenuhnya ke DB via Command.
        */
        $schedule->command('reminder:messages-send')
            ->everyFifteenMinutes()
            ->timezone($defaultTimezone)
            ->withoutOverlapping(10) // Cukup tempelkan lock 10-30 menit agar tidak tabrakan
            ->appendOutputTo(storage_path('logs/scheduler-reminder.log'));

        /*
        |--------------------------------------------------------------------------
        | 5. OVERDUE REMINDER WHATSAPP MESSAGES
        |--------------------------------------------------------------------------
        | Dijalankan setiap 15 menit hanya pada tanggal 25,
        | serta hanya antara jam 08:00 - 22:00 (WIB).
        | Jika kondisi tidak terpenuhi, dilewati (tidak error).
        */
        $schedule->command('reminder:overdue-messages-send')
            ->everyFifteenMinutes()
            ->timezone($defaultTimezone)
            ->when(fn() => now()->day == 25 && now()->hour >= 8 && now()->hour < 22)
            ->onFailure(fn() => LogHelper::logError(null, ['time' => now()], 500, '[Scheduler] reminder:overdue-messages-send failed'))
            ->onSuccess(fn() => LogHelper::logInfo(null, ['time' => now()], 200, '[Scheduler] reminder:overdue-messages-send finished successfully'))
            ->withoutOverlapping(30)
            ->appendOutputTo(storage_path('logs/scheduler-reminder.log'));


        /*
        |--------------------------------------------------------------------------
        | 6. ARCHIVE LARAVEL LOGS
        |--------------------------------------------------------------------------
        | Dijalankan setiap hari pada pukul 23:59,
        | Jika kondisi tidak terpenuhi, dilewati (tidak error).
        */
        $schedule->command('logs:rotate')
            ->everyFiveMinutes()
            ->timezone($defaultTimezone)
            ->withoutOverlapping()
            ->onOneServer()
            ->evenInMaintenanceMode()
            ->appendOutputTo(storage_path('logs/scheduler-rotate.log'));

        $schedule->command('logs:cleanup')
            ->dailyAt('23:59')
            ->timezone($defaultTimezone)
            ->withoutOverlapping()
            ->onOneServer()
            ->evenInMaintenanceMode()
            ->appendOutputTo(storage_path('logs/scheduler-cleanup.log'));


        /*
        |--------------------------------------------------------------------------
        | 7. DELETE OLD MESSAGE LOGS
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
        | 8. FETCH MIKROTIK INFO
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
        | 9. SCAN OLTS
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
        | 10. APPLY SCHEDULED SUBSCRIPTION CHANGES
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
        | 11. PAYMENT CHANNEL UPDATE BROADCAST
        |--------------------------------------------------------------------------
        | Dijalankan setiap 5 jam dari tanggal 1 sampai 15 Juni 2026.
        | Output otomatis di-append ke custom log file.
        | */
        $schedule->command('reminder:payment-channel-update-send')
            ->cron('0 */5 * * *') // 🔄 Menggantikan ->everyFiveHours() dengan standard cron setiap 5 jam
            ->when(function () {
                // 📅 Memastikan scheduler hanya berjalan dari tanggal 1 hingga 15 Juni 2026
                return now()->between('2026-06-01 00:00:00', '2026-06-15 23:59:59');
            })
            ->onFailure(fn() => LogHelper::logError(null, ['time' => now()], 500, '[Scheduler] reminder:payment-channel-update-send failed'))
            ->onSuccess(fn() => LogHelper::logInfo(null, ['time' => now()], 200, '[Scheduler] reminder:payment-channel-update-send finished successfully'))
            ->withoutOverlapping(10)
            ->appendOutputTo(storage_path('logs/scheduler-payment-channel.log'));
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
