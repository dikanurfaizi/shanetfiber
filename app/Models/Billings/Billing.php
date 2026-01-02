<?php

namespace App\Models\Billings;

use App\Models\Customers\Customer;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function billingSubscription(): BelongsTo
    {
        return $this->belongsTo(BillingSubscriptionPeriode::class, 'billing_subscription_id')
            ->withTrashed();
    }

    public function periode()
    {
        return $this->hasOneThrough(
            BillingPeriode::class,
            BillingSubscriptionPeriode::class,
            'id',
            'id',
            'billing_subscription_id',
            'billing_periode_id'
        );
    }

    public function billingPayment()
    {
        return $this->hasOne(BillingPayment::class, 'billing_id')
            ->latestOfMany(); // default by created_at
    }

    public function billingItems(): HasMany
    {
        return $this->hasMany(BillingItem::class, 'billing_id');
    }

    public function customerSubscription()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
