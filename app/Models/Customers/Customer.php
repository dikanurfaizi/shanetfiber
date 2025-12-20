<?php

namespace App\Models\Customers;

use App\Models\Billings\Billing;
use App\Models\Packages\Package;
use App\Models\PppType;
use App\Models\Mikrotik;
use App\Models\User;
use App\Models\Membership\CustomerMembership;
use App\Models\Customers\CustomerSubscription;
use App\Models\Websystem;
use App\Models\AutoIsolir;
use App\Models\PasswordHistory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'wa_opt_out' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function mikrotik(): BelongsTo
    {
        return $this->belongsTo(Mikrotik::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(CustomerSubscription::class);
    }

    public function terminatedSubscription()
    {
        return $this->hasOne(CustomerSubscription::class)
            ->where('status', 'terminated');
    }

    public function getTerminatedPackageAttribute()
    {
        return $this->terminatedSubscription?->package;
    }

    public function pausedSubscription()
    {
        return $this->hasOne(CustomerSubscription::class)
            ->where('status', 'paused');
    }

    public function getPausedPackageAttribute()
    {
        return $this->pausedSubscription?->package;
    }

    // Jika ingin yang aktif saja
    public function activeSubscription()
    {
        return $this->hasOne(CustomerSubscription::class)
            ->where('status', 'active');
    }

    public function getActivePackageAttribute()
    {
        return $this->activeSubscription?->package;
    }

    public function pppType(): BelongsTo
    {
        return $this->belongsTo(PppType::class, 'ppp_type_id');
    }

    public function billing(): HasMany
    {
        return $this->hasMany(Billing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activeMembership()
    {
        return $this->hasOne(CustomerMembership::class)
            ->where('is_active', 1)
            ->latest('id');
    }

    public function customerMembership()
    {
        return $this->activeMembership();
    }

    public function autoIsolir()
    {
        return $this->hasOne(AutoIsolir::class, 'mikrotik_id', 'mikrotik_id');
    }

    // public function isPreviousPassword($plainPassword)
    // {
    //     $previousPasswords = ActivityLog::where('customer_id', $this->id)
    //         ->where('field_name', 'password')
    //         ->where('is_sensitive', true)
    //         ->orderBy('created_at', 'desc')
    //         ->take(5) // Ambil 5 password terakhir
    //         ->pluck('old_value');

    //     foreach ($previousPasswords as $oldHash) {
    //         if (Hash::check($plainPassword, $oldHash)) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    public function passwordHistory()
    {
        return $this->hasMany(PasswordHistory::class);
    }

    // public function activityLogs()
    // {
    //     return $this->hasMany(ActivityLog::class);
    // }

    public function getEmailForPayment(): string
    {
        $fallbackEmail = Websystem::first()->email ?? 'cs_snf@shanetfiber.co.id';

        return $this->email
            ?? optional($this->user)->email
            ?? $fallbackEmail;
    }

    public function pausedSubscriptionWithTrashed()
    {
        return $this->hasOne(CustomerSubscription::class)
            ->withTrashed()
            ->where('status', 'paused');
    }

    public function terminatedSubscriptionWithTrashed()
    {
        return $this->hasOne(CustomerSubscription::class)
            ->withTrashed()
            ->where('status', 'terminated');
    }

    public function lastSubscription()
    {
        return $this->hasOne(CustomerSubscription::class)
            ->latest('id');
    }

    public function getLastPackageAttribute()
    {
        return $this->subscriptions()
            ->latest('id')
            ->first()
            ?->package;
    }

    public function customerSubscriptions()
    {
        return $this->hasMany(CustomerSubscription::class, 'customer_id');
    }

    public function getEstimatedMonthlyIncomingAttribute(): float
    {
        $packagePrice = $this->activeSubscription?->package?->price;

        if ($packagePrice === null) {
            return 0;
        }

        // legacy discount (kalau masih ada)
        $discountPercent = $this->discount ?? 0;

        return $packagePrice * ((100 - $discountPercent) / 100);
    }

    public function addons()
    {
        return $this->hasMany(CustomerAddon::class, 'customer_id');
    }

    public function activeAddons()
    {
        return $this->addons()->where('is_active', 1);
    }
}
