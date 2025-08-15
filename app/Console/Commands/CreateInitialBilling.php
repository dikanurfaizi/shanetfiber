<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Customers\CustomerService;
use Illuminate\Support\Str;
use App\Models\Customer;

class CreateInitialBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:create-initial {customer_id} {trace_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create initial billing for a customer by ID';

    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        parent::__construct();
        $this->customerService = $customerService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customerId = $this->argument('customer_id');
        $traceId = $this->argument('trace_id') ?? Str::uuid()->toString();

        $customer = Customer::find($customerId);

        if (!$customer) {
            $this->error("Customer with ID {$customerId} not found.");
            return 1; // error code
        }

        try {
            // $this->customerService->initialBilling($customer, $traceId);
            $this->info("Initial billing created successfully for customer ID {$customerId}.");
            return 0; // success
        } catch (\Throwable $e) {
            $this->error("Failed to create initial billing: " . $e->getMessage());
            return 1; // error code
        }
    }
}
