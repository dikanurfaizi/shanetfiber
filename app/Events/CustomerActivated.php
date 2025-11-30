<?php

namespace App\Events;

use App\Models\Customer;
use App\Models\Billing\Billing;
use App\Models\Receipt;
use App\Helpers\LogHelper;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerActivated
{
    use Dispatchable, SerializesModels;

    public Customer $customer;
    public Billing $billing;
    public Receipt $receipt;
    public string $traceId;

    public function __construct(Customer $customer, Billing $billing, Receipt $receipt, string $traceId)
    {
        $this->customer = $customer;
        $this->billing = $billing;
        $this->receipt = $receipt;
        $this->traceId = $traceId;
    }
}
