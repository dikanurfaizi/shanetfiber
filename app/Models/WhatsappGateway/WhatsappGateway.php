<?php

namespace App\Models\WhatsappGateway;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappGateway extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [
        'id',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* A profile belongs to a user.
    *
    * @return mixed
    */
    public function whatsapp_number()
    {
        return $this->belongsTo(\App\Models\WhatsappGateway\WhatsappNumber::class);
    }
}
