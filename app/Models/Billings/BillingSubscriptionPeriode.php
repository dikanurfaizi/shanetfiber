<?php

namespace App\Models\Billings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Customers\CustomerSubscription;

class BillingSubscriptionPeriode extends Model
{
    use SoftDeletes;

    protected $table = 'billing_subscription_periodes';

    protected $fillable = [
        'customer_subscription_id',
        'billing_periode_id',
        'package_id_before',
        'package_id_after',
        'package_changed_at',
        'status',
    ];

    public function customerSubscription(): BelongsTo
    {
        return $this->belongsTo(CustomerSubscription::class, 'customer_subscription_id');
    }

    public function billingPeriode(): BelongsTo
    {
        return $this->belongsTo(BillingPeriode::class, 'billing_periode_id');
    }

    public function billings()
    {
        return $this->hasMany(Billing::class, 'billing_subscription_id');
    }
}
