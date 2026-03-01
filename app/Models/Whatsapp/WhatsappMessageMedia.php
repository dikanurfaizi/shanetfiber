<?php

namespace App\Models\Whatsapp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappMessageMedia extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_message_media';

    protected $fillable = [
        'message_id',
        'wa_media_id',
        'file_name',
        'mime_type',
        'file_size',
        'sha256',
        'storage_disk',
        'storage_path',
        'download_status',
        'download_error',
        'downloaded_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'downloaded_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(WhatsappWebhookMessage::class, 'message_id');
    }

    public function isDownloaded(): bool
    {
        return $this->download_status === 'downloaded';
    }
}
