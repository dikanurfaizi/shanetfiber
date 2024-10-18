<?php

namespace App\View\Components\Layouts\Cpanel\Billings;

use App\Models\Websystem;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

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
    public function render(): View|Closure|string
    {
        return view('components.layouts.cpanel.billings.app-layout');
    }
}
