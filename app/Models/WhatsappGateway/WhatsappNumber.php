<?php

namespace App\Models\WhatsappGateway;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappNumber extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [
        'id',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function whatsapp_messages(): HasMany
    {
        return $this->hasMany(\App\Models\WhatsappGateway\WhatsappMessage::class);
    }
    public function whatsapp_gateways(): HasMany
    {
        return $this->hasMany(\App\Models\WhatsappGateway\WhatsappGateway::class);
    }

    public function getNameNumberAttribute()
    {
        return $this->name . ' - ' . $this->number;
    }
}
