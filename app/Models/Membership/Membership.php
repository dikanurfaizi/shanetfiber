<?php

namespace App\Models\Membership;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'discount_type',
        'discount_value',
        'is_active',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function customers()
    {
        return $this->hasMany(CustomerMembership::class);
    }

    public function getStatusLabel()
    {
        return $this->is_active == true ? 'Active' : 'Inactive';
    }

    public function getStatusClass()
    {
        return $this->is_active == true ? 'bg-success' : 'bg-danger';
    }

    public function getFormattedDiscountAttribute()
    {
        // NONE
        if ($this->discount_type === 'none') {
            return '-';
        }

        // PERCENT
        if ($this->discount_type === 'percent') {
            return number_format($this->discount_value, 2, ',', '.') . ' %';
        }

        // AMOUNT (RUPIAH)
        if ($this->discount_type === 'amount') {
            return 'Rp ' . number_format($this->discount_value, 2, ',', '.');
        }

        return $this->discount_value;
    }

    public function getDiscountTypeLabelAttribute()
    {
        return match ($this->discount_type) {
            'percent' => 'Percent (%)',
            'amount'  => 'Amount (Rp)',
            default   => 'None'
        };
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
