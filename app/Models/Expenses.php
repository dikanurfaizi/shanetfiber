<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use HasFactory;
    public $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeVisibleFor($query, $user)
    {
        if ($user->hasRole('super-admin')) {
            return $query;
        }

        return $query->where('created_by', $user->id);
    }
}
