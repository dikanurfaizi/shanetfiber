<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Paket\Paket;
use App\Models\Billing\BillingSubscriptionPeriode;

class CustomerSubscription extends Model
{
    use SoftDeletes;

    protected $table = 'customer_subscriptions';

    protected $fillable = [
        'customer_id',
        'paket_id',
        'subscription_duration_id',
        'status',
        'start_date',
        'end_date',
        'price_final',
        'discount_type_final',
        'discount_value_final',
        'terminated_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price_final' => 'decimal:2',
        'discount_value_final' => 'decimal:2',
        'terminated_at' => 'date'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    public function subscriptionDuration(): BelongsTo
    {
        return $this->belongsTo(SubscriptionDuration::class, 'subscription_duration_id');
    }

    public function billingSubscriptionPeriodes(): HasMany
    {
        return $this->hasMany(BillingSubscriptionPeriode::class, 'customer_subscription_id');
    }
}
