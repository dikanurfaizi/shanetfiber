<?php

namespace App\View\Components\Layouts\Cpanel\Billings;

use App\Models\Bank;
use Closure;
use App\Models\Customers\Customer;
use Illuminate\View\Component;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class Navigation extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $banks = Bank::orderBy('name', 'ASC')->where('disabled', 'false')->get();
        // $customers = Customer::orderBy('address', 'ASC')->where('address', '!=', NULL)->get();
        // $addressUnique = $customers->unique('address');
        // $address = $addressUnique->pluck('address', 'address');
        $address = Customer::orderBy('address', 'ASC')->where('address', '!=', NULL)->pluck('address', 'address');
        $billingsPeriode = BillingPeriode::all();
        $months = BillingPeriode::orderBy('created_at', 'DESC')->pluck('month', 'month');
        $years = BillingPeriode::orderBy('created_at', 'DESC')->pluck('year', 'year');
        // $billings = Billing::orderBy('due_date', )
        $tellers = User::pluck('name', 'name');
        return view('components.layouts.cpanel.billings.navigation', compact('years', 'months', 'address', 'banks', 'tellers'));
    }
}
