<?php

namespace App\Models\Membership;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customers\Customer;

class CustomerMembership extends Model
{
    protected $fillable = [
        'customer_id',
        'membership_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];


    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
