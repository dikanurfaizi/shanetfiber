<?php

namespace App\Models\Customers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Packages\Package;
use App\Models\Packages\PackageDuration;
use App\Models\Billings\BillingSubscriptionPeriode;
use Carbon\Carbon;

class CustomerSubscription extends Model
{
    use SoftDeletes;

    protected $table = 'customer_subscriptions';

    protected $fillable = [
        'customer_id',
        'package_id',
        'package_duration_id',
        'status',
        'start_date',
        'end_date',
        'price_final',
        'discount_type_final',
        'discount_value_final',
        'terminated_at',
        'is_prorated',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price_final' => 'decimal:2',
        'discount_value_final' => 'decimal:2',
        'terminated_at' => 'date',
        'is_prorated' => 'boolean',
    ];

    protected $appends = ['computed_status'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function packageDuration(): BelongsTo
    {
        return $this->belongsTo(PackageDuration::class, 'package_duration_id');
    }

    public function billingSubscriptionPeriodes(): HasMany
    {
        return $this->hasMany(BillingSubscriptionPeriode::class, 'customer_subscription_id');
    }

    public function isExpired(): bool
    {
        if (!$this->end_date) {
            return false; // tidak pernah expired
        }

        return Carbon::parse($this->end_date)->isPast();
    }

    public function getComputedStatusAttribute(): string
    {
        if ($this->status === 'terminated') {
            return 'TERMINATED';
        }

        if ($this->isExpired()) {
            return 'EXPIRED';
        }

        return strtoupper($this->status); // ACTIVE / PAUSED
    }

    public static function latestForCustomer(int $customerId): ?self
    {
        return self::where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->lockForUpdate()
            ->first();
    }

    public function eligibleForBilling(Customer $customer): bool
    {
        // intent utama dari customer
        if ($customer->disabled !== 'false' || $customer->activation !== 'true') {
            return false;
        }

        // status apapun selama customer aktif → lanjut
        return true;
    }

    public function shouldBeReactivated(Customer $customer): bool
    {
        if ($customer->disabled !== 'false' || $customer->activation !== 'true') {
            return false;
        }

        return in_array($this->status, ['paused', 'terminated'], true);
    }

    public function isContractExpired(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        // hanya dianggap expired kalau status active & lewat
        return $this->status === 'active'
            && Carbon::parse($this->end_date)->isPast();
    }

    /**
     * Helper untuk mengecek apakah subscription saat ini bernilai prorate
     */
    public function isProrated(): bool
    {
        return (bool) $this->is_prorated;
    }

    /**
     * Menghitung harga normal paket berdasarkan diskon durasi.
     */
    public function calculateNormalPrice(): array
    {
        $basePrice = (float) ($this->package?->price ?? 0);
        $discountType = $this->packageDuration?->discount_type ?? 'none';
        $discountValue = (float) ($this->packageDuration?->discount_value ?? 0);

        $finalPrice = $basePrice;

        if ($discountType === 'percent' && $discountValue > 0) {
            $finalPrice = max(0, $basePrice - ($basePrice * ($discountValue / 100)));
        } elseif ($discountType === 'amount' && $discountValue > 0) {
            $finalPrice = max(0, $basePrice - $discountValue);
        } else {
            $discountType = 'none';
            $discountValue = 0.00;
        }

        return [
            'base_price'     => $basePrice,
            'final_price'    => $finalPrice,
            'discount_type'  => $discountType,
            'discount_value' => $discountValue,
        ];
    }
}
