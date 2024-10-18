<?php

namespace App\View\Components\Layouts;

use App\Models\Websystem;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Title extends Component
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
        $titleNav = Websystem::where('slug', 'websystem')->value('title');
        return view('components.layouts.title', compact('titleNav'));
    }
}
