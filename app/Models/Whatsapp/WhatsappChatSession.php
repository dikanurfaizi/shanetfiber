<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Model;

class WhatsAppChatSession extends Model
{
    protected $table = 'whatsapp_chat_sessions';

    protected $fillable = [
        'phone_number',
        'customer_id',
        'device_id',
        'chat_mode',
        'last_human_activity_at',
    ];

    protected $casts = [
        'last_human_activity_at' => 'datetime',
    ];
}
