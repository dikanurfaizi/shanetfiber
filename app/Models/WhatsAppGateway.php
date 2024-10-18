<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppGateway extends Model
{
    use HasFactory;

    // Tentukan nama tabel yang benar di database
    protected $table = 'whatsapp_gateways';

    // Allows mass assignment for all fields
    protected $guarded = [];
}
