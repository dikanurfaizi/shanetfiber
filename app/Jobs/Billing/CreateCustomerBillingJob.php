<?php

namespace App\Jobs\Billing;

use App\Models\Billing\Billing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateCustomerBillingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Billing $billing;
    private string $customer_id;
    //private string $secretId;
    /**
     * Create a new job instance.
     */
    public function __construct(Billing $billing, string $customer_id)
    {
        $this->billing = $billing;
        $this->customer_id = $customer_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
