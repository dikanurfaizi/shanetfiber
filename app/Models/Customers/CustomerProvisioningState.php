<?php

namespace App\Models\Customers;

use App\Models\Customers\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProvisioningState extends Model
{
    use HasFactory;

    protected $table = 'customer_provisioning_states';

    protected $fillable = [
        'customer_id',
        'in_radius',
        'in_mikrotik',
        'radius_status',
        'mikrotik_status',
        'last_radius_sync_at',
        'last_mikrotik_sync_at',
        'last_error_message',
    ];

    protected $casts = [
        'in_radius'             => 'boolean',
        'in_mikrotik'           => 'boolean',
        'last_radius_sync_at'   => 'datetime',
        'last_mikrotik_sync_at' => 'datetime',
    ];

    /**
     * Relasi balik ke Model Customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
