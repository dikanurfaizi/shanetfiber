<?php

namespace App\Policies;

use App\Models\Customers\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function customerEdit(User $user, Customer $customer): bool
    {
        return $user->can('customer-edit');
    }

    public function billingEdit(User $user, Customer $customer): bool
    {
        // permission dasar
        if (!$user->can('billing-edit')) {
            return false;
        }

        // ambil billing terakhir
        $latestBilling = $customer->billing()
            ->orderByDesc('id')
            ->first();

        // belum pernah billing → boleh setup
        if (!$latestBilling) {
            return true;
        }

        // hanya boleh edit kalau billing terakhir LS
        return $latestBilling->status === 'LS';
    }
}
