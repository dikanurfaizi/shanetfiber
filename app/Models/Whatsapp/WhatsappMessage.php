<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'whatsapp_messages';

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];


    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    public function whatsapp_number(): BelongsTo
    {
        return $this->belongsTo(WhatsAppNumber::class);
    }
}
