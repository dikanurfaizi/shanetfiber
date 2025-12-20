<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptItem extends Model
{
    protected $fillable = [
        'receipt_id',
        'type',
        'ref_id',
        'name',
        'qty',
        'unit_price',
        'subtotal',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }
}
