<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customers\Customer;

class WhatsAppIncomingMessage extends Model
{
    protected $table = 'whatsapp_incoming_messages';

    protected $fillable = [
        'trace_id',
        'phone_number',
        'customer_id',
        'message_text',
        'device_id',
        'resolved_keyword_slug',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
