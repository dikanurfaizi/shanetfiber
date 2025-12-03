<?php

namespace App\Models;

use App\Models\Billing\Billing;
use App\Models\Packages\Package;
use App\Models\PppType;
use App\Models\Mikrotik;
use App\Models\User;
use App\Models\Member;
use App\Models\CustomerSubscription;
use App\Models\Websystem;
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

    // Tetap pertahankan relasi ke paket utama
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class)->withDefault([
            'name' => 'No Package',
            'price' => 0
        ]);
    }

    public function customerSubscriptions()
    {
        return $this->hasMany(CustomerSubscription::class, 'customer_id');
    }

    // Jika ingin yang aktif saja
    public function activeCustomerSubscriptions()
    {
        return $this->hasMany(CustomerSubscription::class, 'customer_id')->where('status', 'active');
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

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
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
}
