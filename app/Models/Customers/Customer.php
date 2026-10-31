<?php

namespace App\Models\Customers;

use App\Models\AutoIsolir;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPayment;
use App\Models\Customers\CustomerSubscription;
use App\Models\Membership\CustomerMembership;
use App\Models\Messaging\MessageEvent;
use App\Models\Mikrotik;
use App\Models\Packages\Package;
use App\Models\PasswordHistory;
use App\Models\PppType;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Websystem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password'   => 'hashed',
        'is_active'  => 'boolean',
        'activation' => 'boolean',
        'is_opt_out' => 'boolean',
    ];

    // =========================================================================
    // LOCAL QUERY SCOPES (Satu Sumber Kebenaran Kriteria Customer)
    // =========================================================================

    /**
     * Scope: Filter customer yang aktif, sudah aktivasi, tidak opt-out, dan punya nomor HP valid.
     */
    public function scopeEligibleForBroadcast(Builder $query): Builder
    {
        return $query->where('customers.is_active', true)
            ->where('customers.activation', true)
            ->where('customers.is_opt_out', false)
            ->whereNotNull('customers.contact')
            ->where('customers.contact', '!=', '');
    }

    /**
     * Scope: Exclude customer yang tercatat gagal pesan dikarenakan nomor WA/JID Invalid.
     */
    public function scopeWhereValidJid(Builder $query, string $messageType): Builder
    {
        $invalidIds = DB::table('message_events')
            ->where('message_type', $messageType)
            ->where('status', 'failed')
            ->where('response_code', 'INVALID_JID')
            ->whereNotNull('customer_id')
            ->distinct()
            ->pluck('customer_id');

        return $query->when($invalidIds->isNotEmpty(), function ($q) use ($invalidIds) {
            $q->whereNotIn('customers.id', $invalidIds);
        });
    }

    /**
     * Scope: Mencegah duplikasi pesan, pastikan customer belum pernah menerima pesan tipe ini (status success/pending).
     */
    public function scopeWhereUnsentMessageType(Builder $query, string $messageType): Builder
    {
        return $query->whereDoesntHave('messageEvents', function ($sub) use ($messageType) {
            $sub->where('message_type', $messageType)
                ->whereIn('status', ['success', 'pending']);
        });
    }

    // =========================================================================
    // RELASI & HELPER METHODS LAINNYA
    // =========================================================================

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

    // =========================================================================
    // RELASI BILLING & RECEIPT
    // =========================================================================

    public function billing(): HasMany
    {
        return $this->hasMany(Billing::class);
    }

    /**
     * Mendapatkan Billing terbaru milik Customer
     */
    public function latestBilling(): HasOne
    {
        return $this->hasOne(Billing::class, 'customer_id')->latestOfMany('id');
    }

    /**
     * Mendapatkan Receipt terbaru milik Customer (melalui Billing)
     */
    public function latestReceipt(): HasOneThrough
    {
        return $this->hasOneThrough(
            Receipt::class,
            BillingPayment::class,
            'billing_id',      // Foreign key di tabel billing_payments
            'billing_payment_id', // Foreign key di tabel receipts (sesuaikan jika id)
            'id',              // Local key di tabel billings
            'id'               // Local key di tabel billing_payments
        )->latestOfMany();
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

    public function passwordHistory()
    {
        return $this->hasMany(PasswordHistory::class);
    }

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

    public function getUptimeHumanAttribute(): ?string
    {
        if (!$this->uptime_seconds) return null;

        $s = $this->uptime_seconds;

        $d = intdiv($s, 86400);
        $s %= 86400;
        $h = intdiv($s, 3600);
        $s %= 3600;
        $m = intdiv($s, 60);
        $s %= 60;

        return trim(sprintf(
            '%s%s%s%s',
            $d ? "$d day " : '',
            $h ? "$h hour " : '',
            $m ? "$m minute " : '',
            $s ? "$s second" : ''
        ));
    }

    public function activeAddonsIndexed(): Collection
    {
        return $this->activeAddons()
            ->get()
            ->mapWithKeys(function ($addon) {
                return [
                    $addon->addon_id => (object) [
                        'addon_id'   => $addon->addon_id,
                        'qty'        => (int) $addon->qty,
                        'unit_price' => (int) $addon->unit_price,
                        'subtotal'   => (int) $addon->subtotal,
                    ]
                ];
            });
    }

    public function messageEvents(): HasMany
    {
        return $this->hasMany(MessageEvent::class, 'customer_id');
    }

    public function latestSubscription()
    {
        return $this->hasOne(CustomerSubscription::class, 'customer_id')->latestOfMany('created_at');
    }

    public function currentSubscription()
    {
        $activeSubscriptions = $this->customerSubscriptions()
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($activeSubscriptions->isNotEmpty()) {
            return $activeSubscriptions->first();
        }

        return $this->customerSubscriptions()
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function provisioningState(): HasOne
    {
        return $this->hasOne(CustomerProvisioningState::class, 'customer_id');
    }
}
