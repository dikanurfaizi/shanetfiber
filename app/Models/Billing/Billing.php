<?php

namespace App\Models\Billing;

use App\Models\Customer;
use App\Models\Billing\BillingSubscriptionPeriode;
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

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id')->withTrashed();
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }

    public function billingSubscription(): BelongsTo
    {
        return $this->belongsTo(BillingSubscriptionPeriode::class, 'billing_subscription_id');
    }
}
