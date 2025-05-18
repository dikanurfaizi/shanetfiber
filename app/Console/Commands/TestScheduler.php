<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestScheduler extends Command
{
    protected $signature = 'test:scheduler';
    protected $description = 'Log a message every time the scheduler runs';

    public function handle()
    {
        Log::info("✅ Scheduler berhasil dijalankan pada " . now());
        $this->info('Test scheduler executed');
    }
}
