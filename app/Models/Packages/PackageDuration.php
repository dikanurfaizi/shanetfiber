<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Customers\CustomerSubscription;

class PackageDuration extends Model
{
    protected $table = 'package_durations';

    protected $fillable = [
        'package_id',
        'duration_id',
        'discount_type',
        'discount_value',
        'is_active',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function duration()
    {
        return $this->belongsTo(DurationLabel::class, 'duration_id');
    }

    public function SubsDuration(): HasMany
    {
        return $this->hasMany(CustomerSubscription::class, 'subscription_duration_id');
    }
}
