<?php

namespace App\Models;

use App\Models\Billing\Billing;
use App\Models\Paket;
use App\Models\PppType;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Authenticatable
{
    //use HasFactory;
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;
    //public $guarded = [];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /* protected $fillable = [
        'name',
        'email',
        'password',
        'mikrotik_id',
        'paket_id',
        'password_ppp',
        'secret_id',
        'address',
        'contact',
        'slug',
        'username',
        'ppp_type_id',
        'disabled',
        'activation_date',
        'activation',
        //'user_id'
    ];*/
    public $guarded = [];
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function mikrotik(): BelongsTo
    {
        return $this->belongsTo(Mikrotik::class);
    }
    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    public function ppp_type(): BelongsTo
    {
        return $this->belongsTo(PppType::class);
    }

    function billing(): HasMany
    {
        return $this->hasMany(Billing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    // public function user(): BelongsTo
    // {
    //     return $this->belongsTo(User::class);
    // }
}
