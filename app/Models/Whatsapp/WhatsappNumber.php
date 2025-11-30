<?php

namespace App\Models\Whatsapp;

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
        return $this->hasMany(WhatsAppMessage::class);
    }

    public function account()
    {
        return $this->belongsTo(WhatsappGatewayAccount::class);
    }

    public function getNameNumberAttribute()
    {
        return $this->name.' - '.$this->number;
    }
}
