<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Console\Commands\BillingCheckAndUpdateProfile;
use App\Console\Commands\SendMessages;

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
    ];
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
        // $schedule->command('messages:send')
        //     ->cron('* * * * *')  // Menjalankan setiap menit untuk pengujian
        //     ->when(function () {
        //         return true;  // Perintah ini akan selalu dijalankan
        //     });
        // $schedule->call(function () {
        //     DB::table('billing_periodes')->create([
        //         'name' => 'Test'
        //     ]);
        // })->everyMinute();
        //$schedule->command('log:archive-large')->everyFiveMinutes();
        //  $schedule->command('messages:send')
        //     ->withoutOverlapping() // Mencegah eksekusi ganda jika belum selesai
        //     ->onOneServer()        // Hanya dijalankan pada satu server jika menggunakan load balancer
        //     ->when(function () {
        //         // Kondisi agar hanya dijalankan saat diperlukan
        //         return true; // Logika bisa disesuaikan
        //     });
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
