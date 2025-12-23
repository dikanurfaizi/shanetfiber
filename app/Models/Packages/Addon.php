<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $table = 'addons';

    protected $fillable = [
        'slug',
        'name',
        'price',
        'is_recurring',
        'is_active',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_recurring' => 'boolean',
        'is_active'    => 'boolean',
    ];

    // Route Model Binding pakai slug
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getStatusLabel()
    {
        return $this->is_active == true ? 'Enabled' : 'Disabled';
    }

    public function getStatusClass()
    {
        return $this->is_active == true ? 'bg-success' : 'bg-danger';
    }

    public function isActive(): bool
    {
        return $this->is_active == true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
