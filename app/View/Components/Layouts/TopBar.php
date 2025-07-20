<?php

namespace App\View\Components\Layouts;

use App\Models\Billing\Billing;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Helpers\UserAuth;

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
        $auth = UserAuth::getUserDetails();
        $user = $auth['user'];

        $billings = Billing::with([
            'customer' => fn($q) => $q->whereNull('deleted_at'),
            'customer.user'
        ])
            ->whereHas('customer', fn($q) => $q->whereNull('deleted_at'))
            ->get();

        if ($user->can('view-bill-top-bar-total')) {
            $billingsToShow = $billings->filter(
                fn($billing) =>
                !in_array($billing->status, ['LS', 'NP'])
            );
        } else {
            $billingsToShow = $billings->filter(
                fn($billing) =>
                $billing->customer &&
                    $billing->customer->user_id == $user->id &&
                    !in_array($billing->status, ['LS', 'NP'])
            );
        }

        return view('components.layouts.top-bar', compact('billingsToShow'));
    }
}
