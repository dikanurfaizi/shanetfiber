<?php

namespace App\View\Components\Layouts\Customers;

use Closure;
use App\Models\Websystem;
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
        $websystem = Websystem::where('slug', 'websystem')->first();
        $paymentGateway = Websystem::where('slug', 'websystem')->value('payment_gateway');
        return view('components.layouts.customers.navigation', compact('paymentGateway', 'websystem'));
    }
}
