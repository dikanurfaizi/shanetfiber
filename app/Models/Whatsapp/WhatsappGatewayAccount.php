<?php

namespace App\Models\Whatsapp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappGatewayAccount extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function gateway()
    {
        return $this->belongsTo(WhatsappGateway::class);
    }

    public function numbers()
    {
        return $this->hasMany(WhatsappNumber::class);
    }

    public function defaultNumber()
    {
        return $this->hasOne(WhatsappNumber::class, 'whatsapp_gateway_account_id')->where('is_default', true);
    }

    public function primaryNumber()
    {
        return $this->hasOne(WhatsappNumber::class)->where('is_default', 1);
    }

    public function getDisplayNumberAttribute()
    {
        return $this->meta_waba_id
            ?? optional($this->primaryNumber)->number
            ?? '-';
    }
}
