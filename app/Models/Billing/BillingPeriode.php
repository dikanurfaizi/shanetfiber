<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Billing\BillingSubscriptionPeriode;

class BillingPeriode extends Model
{
    use HasFactory;
    public $guarded = [];

    public function billingSubscriptionPeriodes()
    {
        return $this->hasMany(BillingSubscriptionPeriode::class, 'billing_periode_id');
    }
}
