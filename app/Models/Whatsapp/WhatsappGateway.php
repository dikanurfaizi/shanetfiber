<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppGateway extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'whatsapp_gateways';

    protected $guarded = [
        'id',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Relasi ke Accounts milik Gateway ini
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(WhatsAppGatewayAccount::class, 'whatsapp_gateway_id');
    }

    /**
     * Relasi ke semua WhatsApp Number melalui WhatsAppGatewayAccount
     */
    public function numbers(): HasManyThrough
    {
        return $this->hasManyThrough(
            WhatsAppNumber::class,
            WhatsAppGatewayAccount::class,
            'whatsapp_gateway_id',         // Foreign key di tabel whatsapp_gateway_accounts
            'whatsapp_gateway_account_id', // Foreign key di tabel whatsapp_numbers
            'id',                           // Local key di tabel whatsapp_gateways
            'id'                            // Local key di tabel whatsapp_gateway_accounts
        );
    }
}
