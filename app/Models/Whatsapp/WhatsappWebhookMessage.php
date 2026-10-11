<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppWebhookMessage extends Model
{
    protected $table = 'whatsapp_webhook_messages';

    protected $fillable = [
        'conversation_id',
        'wa_message_id',
        'trace_id',
        'direction',
        'from_number',
        'to_number',
        'type',
        'text_body',
        'timestamp_wa',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
        'last_status',
        'payload',
    ];

    protected $casts = [
        'timestamp_wa' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
        'payload' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsAppConversation::class, 'conversation_id');
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(
            WhatsAppMessageStatus::class,
            'wa_message_id',
            'wa_message_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isIncoming(): bool
    {
        return $this->direction === 'incoming';
    }

    public function isOutgoing(): bool
    {
        return $this->direction === 'outgoing';
    }

    public function markStatus(string $status): void
    {
        $this->update([
            'last_status' => $status
        ]);
    }

    public function media()
    {
        return $this->hasMany(
            WhatsAppMessageMedia::class,
            'message_id',   // foreign key di tabel whatsapp_message_media
            'id'            // local key di tabel parent (biasanya id)
        )->where('download_status', 'downloaded');
    }
}
