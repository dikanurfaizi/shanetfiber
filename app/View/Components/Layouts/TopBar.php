<?php

namespace App\View\Components\Layouts;

use App\Models\Billings\Billing;
use App\Models\WhatsApp\WhatsAppConversation;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Helpers\UserAuth;
use App\Models\InvestorAdmin;

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

        // --- LOGIKA MENGHITUNG UNREAD CHAT BERDASARKAN ROLE ---
        $unreadChatQuery = WhatsAppConversation::where('has_unread', true);

        // Cek apakah user saat ini MEMILIKI role Kasir
        $isKasir = $user->hasRole('Kasir');
        $isSuperAdmin = $user->hasRole('Super Admin');

        // Jika BUKAN Kasir dan BUKAN Super Admin (Artinya dia Admin biasa), filter hanya chat milik customernya saja
        if (!$isKasir && !$isSuperAdmin) {
            $unreadChatQuery->whereHas('customer', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $unreadChatCount = $unreadChatQuery->count();
        // -----------------------------------------------------

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
                fn($billing) => !in_array($billing->status, ['LS', 'VD'])
            );
        } elseif ($user->can('investor-billing-total')) {
            // Investor: ambil semua admin yang berelasi dengan investor ini
            $linkedAdminIds = InvestorAdmin::where('investor_id', $user->id)
                ->pluck('admin_id')
                ->toArray();

            // Termasuk dirinya sendiri
            $visibleUserIds = array_merge([$user->id], $linkedAdminIds);

            // Filter billing berdasarkan user_id customer yang visible
            $billingsToShow = $billings->filter(
                fn($billing) =>
                $billing->customer &&
                    in_array($billing->customer->user_id, $visibleUserIds) &&
                    !in_array($billing->status, ['LS', 'VD'])
            );
        } else {
            // Default: user biasa, hanya billing miliknya sendiri
            $billingsToShow = $billings->filter(
                fn($billing) =>
                $billing->customer &&
                    $billing->customer->user_id == $user->id &&
                    !in_array($billing->status, ['LS', 'VD'])
            );
        }

        return view('components.layouts.top-bar', compact('billingsToShow', 'unreadChatCount'));
    }
}
