<?php

namespace App\Models\Customers;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerLocationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'latitude',
        'longitude',
        'address',
        'created_by',
    ];

    /**
     * Relasi balik ke Model Customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relasi ke User yang melakukan pengubahan (opsional)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
