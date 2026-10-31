<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Expenses\ExpenseItem;
use App\Models\Customers\Customer;
use App\Models\AutoIsolir;

class Mikrotik extends Model
{
    use HasFactory;

    public $guarded = [];

    protected $attributes = [
        'coa_port' => 3799,
        'port'     => 8728,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenseItems(): HasMany
    {
        return $this->hasMany(ExpenseItem::class, 'mikrotik_id');
    }

    /**
     * Relasi ke AutoIsolir (One to One)
     */
    public function autoIsolir(): HasOne
    {
        return $this->hasOne(AutoIsolir::class, 'mikrotik_id');
    }

    /**
     * Relasi ke Customer (One to Many)
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'mikrotik_id');
    }
}
