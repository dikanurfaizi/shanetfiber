<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Model;
use App\Models\Mikrotik;
use App\Models\Customers\CustomerSubscription;
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

    public function packageDurations()
    {
        return $this->hasMany(PackageDuration::class, 'package_id');
    }

    public function customerSubscriptions()
    {
        return $this->hasMany(CustomerSubscription::class, 'package_id');
    }

    public function category()
    {
        return $this->belongsTo(PackageCategories::class, 'package_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('disabled', 'false');
    }

    public function isActive(): bool
    {
        return $this->disabled === 'false';
    }
}
