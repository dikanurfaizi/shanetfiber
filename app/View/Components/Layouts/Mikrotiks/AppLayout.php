<?php

namespace App\View\Components\Layouts\Mikrotiks;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Websystem;

class AppLayout extends Component
{
    /**
     * Create a new component instance.
     */
    public mixed $title;
    public function __construct($title = 'Customer Management')
    {
        $titleWeb = Websystem::where('slug', 'websystem')->value('title');
        $this->title = $title . ' | ' . $titleWeb;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.layouts.mikrotiks.app-layout');
    }
}
