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
        'stage',
        'response_code',
        'response_message',
        'message_id',
        'request_payload',
        'response_payload',
        'retry_count',
        'last_error_at',
        'trace_id',
        'sent_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'sent_at' => 'datetime',
        'last_error_at' => 'datetime',
        'retry_count' => 'integer',
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
