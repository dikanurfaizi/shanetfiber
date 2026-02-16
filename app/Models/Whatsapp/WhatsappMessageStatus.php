<?php

namespace App\Models\Whatsapp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessageStatus extends Model
{
    protected $fillable = [
        'wa_message_id',
        'status',
        'status_at',
        'raw_payload',
    ];

    protected $casts = [
        'status_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function message(): BelongsTo
    {
        return $this->belongsTo(
            WhatsappWebhookMessage::class,
            'wa_message_id',
            'wa_message_id'
        );
    }
}
