<?php

namespace App\Models\Whatsapp\Cloud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappCloudTemplateButton extends Model
{
    protected $table = 'whatsapp_cloud_template_buttons';

    protected $fillable = [
        'component_id',
        'type',
        'text',
        'url',
        'phone_number',
        'payload',
        'code',
        'position',
        'raw_button',
        'example',
    ];

    protected $casts = [
        'raw_button' => 'array',
        'example' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function component(): BelongsTo
    {
        return $this->belongsTo(
            WhatsappCloudTemplateComponent::class,
            'component_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isUrl(): bool
    {
        return $this->type === 'URL';
    }

    public function isQuickReply(): bool
    {
        return $this->type === 'QUICK_REPLY';
    }
}
