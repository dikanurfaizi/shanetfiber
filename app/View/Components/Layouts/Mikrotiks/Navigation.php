<?php

namespace App\View\Components\Layouts\Mikrotiks;

use Closure;
use App\Models\Mikrotik;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Navigation extends Component
{
    /**
     * Create a new component instance.
     */
    public $mikrotik;
    public function __construct(Mikrotik $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.layouts.mikrotiks.navigation', [
            'mikrotik' => $this->mikrotik,
        ]);
    }
}
