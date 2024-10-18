<?php

namespace App\View\Components\Layouts\Customers;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppLayout extends Component
{
    /**
     * Create a new component instance.
     */
    public mixed $title;
    public function __construct($title = 'Customer')
    {
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {

        return view('components.layouts.customers.app-layout');
    }
}
