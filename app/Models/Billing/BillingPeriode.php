<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPeriode extends Model
{
    use HasFactory;
    public $guarded = [];

    function billing(): HasMany
    {
        return $this->hasMany(Billing::class);
    }
}
