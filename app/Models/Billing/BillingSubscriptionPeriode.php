<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CustomerSubscription;
use App\Models\Billing\BillingPeriode;

class BillingSubscriptionPeriode extends Model
{
    protected $table = 'billing_subscription_periodes';

    protected $fillable = [
        'customer_subscription_id',
        'billing_periode_id',
        'billing_periode',
        'is_paid',
        'paid_at',
        'is_expired',
        'status'
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'is_expired' => 'boolean'
    ];

    public function customerSubscription(): BelongsTo
    {
        return $this->belongsTo(CustomerSubscription::class, 'customer_subscription_id');
    }

    public function billingPeriode(): BelongsTo
    {
        return $this->belongsTo(BillingPeriode::class, 'billing_periode_id');
    }
}
