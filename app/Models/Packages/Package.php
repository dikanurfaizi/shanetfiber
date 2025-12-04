<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Model;
use App\Models\Mikrotik;
use App\Models\SubscriptionDuration;
use App\Models\CustomerSubscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;
    public $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function mikrotik(): BelongsTo
    {
        return $this->belongsTo(Mikrotik::class);
    }

    public function subscriptionDurations()
    {
        return $this->hasMany(SubscriptionDuration::class, 'package_id');
    }

    public function customerSubscriptions()
    {
        return $this->hasMany(CustomerSubscription::class, 'package_id');
    }

    public function category()
    {
        return $this->belongsTo(PackageCategories::class, 'package_category_id');
    }
}
