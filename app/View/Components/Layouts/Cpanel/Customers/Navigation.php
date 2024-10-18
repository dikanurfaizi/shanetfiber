<?php

namespace App\View\Components\Layouts\Cpanel\Customers;

use Closure;
use App\Models\Customer;
use App\Models\Mikrotik;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

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
        $customers = Customer::orderBy('address', 'ASC')->where('address', '!=', NULL)->get();
        $addressUnique = $customers->unique('address');
        $address = $addressUnique->pluck('address', 'address');
        $allServers = Mikrotik::where('disabled', 'false')->orderBy('name', 'ASC')->pluck('name', 'id');
        return view('components.layouts.cpanel.customers.navigation', compact('address', 'allServers'));
    }
}
