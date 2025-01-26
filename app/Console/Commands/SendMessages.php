<?php

namespace App\Console\Commands;

use App\Jobs\SendPaymentReminderJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class SendMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send messages at 9:00 AM on the 15th, 16th, and 17th of each month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentTime = Carbon::now()->format('Y-m-d H:i:s'); // Mengambil waktu saat ini
        Log::info("Send reminder messages job started at {$currentTime}");

        // Menjalankan pekerjaan pengiriman pesan
        SendPaymentReminderJob::dispatch();

        $completedTime = Carbon::now()->format('Y-m-d H:i:s'); // Mengambil waktu setelah dispatch
        Log::info("Schedule reminder messages finished at {$completedTime}");
        return 0;
    }
}
