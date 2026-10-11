<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppNumber extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'whatsapp_numbers';

    protected $guarded = [
        'id',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function whatsapp_messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class);
    }

    public function account()
    {
        return $this->belongsTo(
            WhatsAppGatewayAccount::class,
            'whatsapp_gateway_account_id' // FOREIGN KEY BENAR
        );
    }

    public function getNameNumberAttribute()
    {
        return $this->name . ' - ' . $this->number;
    }
}
