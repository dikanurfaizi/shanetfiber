<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Jobs\Network\FetchMikrotikInfo;

class FetchMikrotikInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-mikrotik-info-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jobId = Str::uuid()->toString();
        FetchMikrotikInfo::dispatch($jobId)->onQueue('mikrotik-heavy');
    }
}
