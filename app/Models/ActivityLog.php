<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'customer_id',
        'activity_type',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Relasi ke Customer
     */
    public function customer()
    {
        return $this->belongsTo(
            \App\Models\Customers\Customer::class,
            'customer_id'
        );
    }
}
