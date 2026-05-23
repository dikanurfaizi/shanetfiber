<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Customers\Customer;
//use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'disabled',
        'contact',
        'telegram_chat_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    // Investor → melihat admin yang ia pantau
    public function linkedAdmins()
    {
        return $this->belongsToMany(User::class, 'investor_admins', 'investor_id', 'admin_id')
            ->withPivot('linked_at', 'created_by')
            ->withTimestamps();
    }

    // Admin → bisa diketahui siapa saja investornya
    public function linkedInvestors()
    {
        return $this->belongsToMany(User::class, 'investor_admins', 'admin_id', 'investor_id')
            ->withPivot('linked_at', 'created_by')
            ->withTimestamps();
    }

    public function investorAdmins()
    {
        return $this->hasMany(InvestorAdmin::class, 'investor_id');
    }

    /**
     * Mengambil 3 rekening terpusat yang bisa digunakan oleh user ini
     */
    public function banks(): BelongsToMany
    {
        // Laravel otomatis tahu tabel pembantunya bernama bank_user
        return $this->belongsToMany(Bank::class, 'bank_users')->withTimestamps();
    }
}
