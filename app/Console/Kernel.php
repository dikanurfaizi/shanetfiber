<?php

namespace App\Console;

use Commands\CreateBillingCron;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BillingController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\SendPaymentReminderJob;
use App\Services\WhatsappMessageService;
use Illuminate\Support\Facades\Log;
use App\Console\Commands\BillingCheckAndUpdateProfile;

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
    ];
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        Log::info("Starting to schedule commands.");

        // $schedule->command('inspire')->hourly();
        // $schedule->command('create_billing:cron')->everyMinute();
        $schedule->command('messages:send')
            ->withoutOverlapping() // Mencegah eksekusi ganda jika belum selesai
            ->onOneServer()        // Hanya dijalankan pada satu server jika menggunakan load balancer
            ->when(function () {
                // Kondisi agar hanya dijalankan saat diperlukan
                return true; // Logika bisa disesuaikan
            });

        $schedule->command('log:archive-large')->everyFiveMinutes();
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
