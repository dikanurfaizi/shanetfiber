<?php

namespace App\Models\Billing;

use App\Models\Customer;
use App\Models\Billing\BillingPeriode;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Billing extends Model
{
    use HasFactory;
    public $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }


    public function billing_periode(): BelongsTo
    {
        return $this->belongsTo(BillingPeriode::class);
    }
}
