<?php

namespace App\Models\WhatsappGateway;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappMessage extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [
        'id',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    public function whatsapp_number(): BelongsTo
    {
        return $this->belongsTo(\App\Models\WhatsappGateway\WhatsappNumber::class);
    }
}
