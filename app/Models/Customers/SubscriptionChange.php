<?php

namespace App\Models\Customers;

use App\Models\Packages\Package;
use App\Models\Packages\PackageDuration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionChange extends Model
{
    protected $table = 'subscription_changes';

    protected $fillable = [
        'customer_id',
        'old_subscription_id',
        'new_package_id',
        'new_package_duration_id',
        'change_type',
        'effective_at',
        'status',
        'requested_at',
        'requested_by',
        'payload_json',
        'error_message',
    ];

    protected $casts = [
        'effective_at' => 'datetime',
        'requested_at' => 'datetime',
        'payload_json' => 'array',
    ];

    /* ======================================================
     * Relations
     * ====================================================== */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function oldSubscription(): BelongsTo
    {
        return $this->belongsTo(CustomerSubscription::class, 'old_subscription_id');
    }

    public function newPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'new_package_id');
    }

    public function newPackageDuration(): BelongsTo
    {
        return $this->belongsTo(
            PackageDuration::class,
            'new_package_duration_id'
        );
    }

    /* ======================================================
     * Scopes (optional tapi berguna)
     * ====================================================== */

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeEffective($query)
    {
        return $query->where('effective_at', '<=', now());
    }
}
