<?php

namespace App\Models;

use App\Models\Billing\Billing;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    public $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    const STATUS_UNPAID = 1;
    const STATUS_PAID = 2;
    const STATUS_EXPIRED = 3;
    const STATUS_CANCELED = 4;
}
