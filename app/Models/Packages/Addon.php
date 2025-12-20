<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'price',
        'is_recurring',
        'is_active',
    ];
}
