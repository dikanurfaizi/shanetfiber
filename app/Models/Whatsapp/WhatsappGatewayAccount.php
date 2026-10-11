<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WhatsAppGatewayAccount extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_gateway_accounts';

    protected $guarded = [
        'id',
    ];

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(WhatsAppGateway::class, 'whatsapp_gateway_id');
    }

    public function numbers(): HasMany
    {
        return $this->hasMany(WhatsAppNumber::class, 'whatsapp_gateway_account_id');
    }

    public function defaultNumber(): HasOne
    {
        return $this->hasOne(WhatsAppNumber::class, 'whatsapp_gateway_account_id')
            ->where('is_default', true);
    }

    public function primaryNumber(): HasOne
    {
        return $this->hasOne(WhatsAppNumber::class, 'whatsapp_gateway_account_id')
            ->where('is_default', true);
    }

    public function getDisplayNumberAttribute(): string
    {
        return $this->meta_waba_id
            ?? optional($this->primaryNumber)->number
            ?? '-';
    }
}
