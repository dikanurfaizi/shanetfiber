<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Paket\Paket;
use App\Models\CustomerSubscription;

class SubscriptionDuration extends Model
{
    protected $table = 'subscription_durations';

    protected $fillable = [
        'paket_id',
        'name_en',
        'name_id',
        'duration_months',
        'discount_type',
        'discount_value',
        'is_active',
    ];

    protected $casts = [
        'duration_months' => 'integer',
        'discount_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }

    public function customerSubscriptions(): HasMany
    {
        return $this->hasMany(CustomerSubscription::class, 'subscription_duration_id');
    }
}
