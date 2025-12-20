<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customers\Customer;
use App\Models\WhatsApp\WhatsAppGateway;

class MessageEvent extends Model
{
    protected $fillable = [
        'gateway_id',
        'customer_id',
        'channel',
        'message_type',
        'meta',
        'status',
        'response_code',
        'response_message',
        'message_id',
        'trace_id',
        'sent_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'sent_at' => 'datetime',
    ];

    public function gateway()
    {
        return $this->belongsTo(WhatsAppGateway::class, 'gateway_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // === Helpers ===
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
