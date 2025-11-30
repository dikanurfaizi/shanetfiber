<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DeleteOldMessageLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:delete-old-messages';

    /**
     * The console command description.
     */
    protected $description = 'Delete message_logs records older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $thresholdDate = Carbon::now()->subDays(30);

        $this->message_events_delete($thresholdDate);
    }

    private function message_events_delete($thresholdDate)
    {
        $deleted = DB::table('message_events')
            ->where('created_at', '<', $thresholdDate)
            ->where('status', '=','success')
            ->delete();

        $this->info("✅ $deleted old message log(s) deleted (before {$thresholdDate->toDateTimeString()})");
    }
}
