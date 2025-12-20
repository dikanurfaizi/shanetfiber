<?php

namespace App\Models\Customers;

use Illuminate\Database\Eloquent\Model;
use App\Models\Packages\Addon;
use App\Models\Customers\Customer;

class CustomerAddon extends Model
{
    protected $table = 'customer_addons';

    protected $fillable = [
        'customer_id',
        'addon_id',
        'qty',
        'unit_price',
        'subtotal',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // helper
    public function calculateSubtotal()
    {
        return $this->qty * $this->unit_price;
    }
}
