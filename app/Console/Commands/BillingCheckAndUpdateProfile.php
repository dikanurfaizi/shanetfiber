<?php

namespace App\Console\Commands;

use App\Jobs\Billing\CheckBillingAndUpdateProfileSecretJob as BillingCheckBillingAndUpdateProfileSecretJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BillingCheckAndUpdateProfile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:check-and-update-profile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch job untuk pengecekan billing dan update profile Mikrotik';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $traceId = (string) Str::uuid();
        dispatch(new BillingCheckBillingAndUpdateProfileSecretJob($traceId))->onQueue('default');
        $this->info('Job CheckBillingAndUpdateProfileSecretJob sudah dikirim ke queue.');
    }
}
