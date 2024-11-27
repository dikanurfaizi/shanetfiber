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
        $schedule->command('create_billing:cron')->everyMinute();
        // $schedule->call(function () {
        //     DB::table('billing_periodes')->create([
        //         'name' => 'Test'
        //     ]);
        // })->everyMinute();

        // Ambil schedule_time dari database
        $scheduleTime = DB::table('whatsapp_gateways')
            ->where('disabled', false)
            ->whereNotNull('schedule_time')
            ->whereBetween('schedule_time', [0, 23]) // Filter langsung di query
            ->value('schedule_time');

        if ($scheduleTime !== null) {
            $time = str_pad($scheduleTime, 2, '0', STR_PAD_LEFT) . ':00';

            // Tambahkan log sebelum job dijadwalkan
            Log::info("Preparing to schedule SendPaymentReminderJob at {$time}.");

            // Jadwalkan job berdasarkan waktu
            $schedule->job(new SendPaymentReminderJob(app(WhatsappMessageService::class)))->dailyAt($time);
            // $schedule->job(new SendPaymentReminderJob(app(WhatsappMessageService::class)))->everyMinute();

            // Tambahkan log untuk memastikan job telah dijadwalkan
            Log::info("Job scheduled successfully at {$time} based on schedule_time from whatsapp_gateways.");
        } else {
            // Jika tidak ada waktu yang valid, tambahkan log untuk debugging
            Log::warning('Job not scheduled: No valid schedule_time found in whatsapp_gateways.');
        }

        Log::info("Finished scheduling commands.");
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
