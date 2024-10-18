<?php

namespace App\View\Components\Layouts;

use App\Models\Billing\Billing;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TopBar extends Component
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
        $billings = Billing::get();
        return view('components.layouts.top-bar', compact('billings'));
    }
}
