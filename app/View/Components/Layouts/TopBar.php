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

        // Ambil semua billing dengan relasi customer & user
        $billings = Billing::with([
            'customer' => fn($q) => $q->whereNull('deleted_at'),
            'customer.user'
        ])
            ->whereHas('customer', fn($q) => $q->whereNull('deleted_at'))
            ->get();

        if ($user->can('view-bill-top-bar-total')) {
            // Admin global: lihat semua billing kecuali LS & NP
            $billingsToShow = $billings->filter(
                fn($billing) => !in_array($billing->status, ['LS', 'NP'])
            );
        } elseif ($user->can('investor-billing-total')) {
            // Investor: ambil semua admin yang berelasi dengan investor ini
            $linkedAdminIds = \App\Models\InvestorAdmin::where('investor_id', $user->id)
                ->pluck('admin_id')
                ->toArray();

            // Termasuk dirinya sendiri
            $visibleUserIds = array_merge([$user->id], $linkedAdminIds);

            // Filter billing berdasarkan user_id customer yang visible
            $billingsToShow = $billings->filter(
                fn($billing) =>
                $billing->customer &&
                    in_array($billing->customer->user_id, $visibleUserIds) &&
                    !in_array($billing->status, ['LS', 'NP'])
            );
        } else {
            // Default: user biasa, hanya billing miliknya sendiri
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
