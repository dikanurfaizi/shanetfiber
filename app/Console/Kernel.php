<?php

namespace App\Console;

use Commands\CreateBillingCron;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BillingController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        // $schedule->command('inspire')->hourly();
        $schedule->command('create_billing:cron')
            ->everyMinute();
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
