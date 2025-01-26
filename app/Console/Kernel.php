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

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    // protected $commands = [
    //      CreateBillingCron::class,
    //  ];
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        Log::info("Starting to schedule commands.");

        // $schedule->command('inspire')->hourly();
        // $schedule->command('create_billing:cron')->everyMinute();
        // $schedule->command('messages:send')->cron('0 9 15-20 * *')->when(function () {
        //     Log::info("Scheduling messages:send command");
        //     return true;
        // });
        $schedule->command('messages:send')
            ->cron('* * * * *')  // Menjalankan setiap menit untuk pengujian
            ->when(function () {
                return true;  // Perintah ini akan selalu dijalankan
            });
        // $schedule->call(function () {
        //     DB::table('billing_periodes')->create([
        //         'name' => 'Test'
        //     ]);
        // })->everyMinute();
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
