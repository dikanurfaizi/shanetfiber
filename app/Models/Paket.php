<?php

namespace App\Models;


use App\Models\Mikrotik;
use App\Models\Billing\BillingCycle;
use App\Models\Paket\PackageCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paket extends Model
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

    public function billingCycle()
    {
        return $this->belongsTo(BillingCycle::class);
    }

    public function packageCategory(): BelongsTo
    {
        return $this->belongsTo(PackageCategory::class);
    }
}
