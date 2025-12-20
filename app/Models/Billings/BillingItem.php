<?php

namespace App\Models\Billings;

use Illuminate\Database\Eloquent\Model;
use App\Models\Packages\Addon;

class BillingItem extends Model
{
    protected $fillable = [
        'billing_id',
        'item_type',
        'source_type',
        'source_id',
        'item_name',
        'qty',
        'price',
        'subtotal',
    ];

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }

    // Polymorphic-lite resolver
    public function addon()
    {
        return $this->belongsTo(Addon::class, 'item_id')
            ->where('item_type', 'addon');
    }
}
