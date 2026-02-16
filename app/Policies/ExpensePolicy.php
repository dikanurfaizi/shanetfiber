<?php

namespace App\Policies;

use App\Models\Expenses;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExpensePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->can('expense-list');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Expenses $expenses)
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $expenses->created_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->created_by === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Expenses $expenses)
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $expenses->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Expenses $expenses)
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $expenses->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Expenses $expenses)
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $expenses->created_by === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Expenses $expenses)
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $expenses->created_by === $user->id;
    }
}
