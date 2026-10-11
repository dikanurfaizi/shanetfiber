<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessageStatus extends Model
{
    protected $table = 'whatsapp_message_statuses';

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
            WhatsAppWebhookMessage::class,
            'wa_message_id',
            'wa_message_id'
        );
    }
}
