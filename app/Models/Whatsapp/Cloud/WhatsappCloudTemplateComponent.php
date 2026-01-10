<?php

namespace App\Models\Whatsapp\Cloud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappCloudTemplateComponent extends Model
{
    protected $table = 'whatsapp_cloud_template_components';

    protected $fillable = [
        'template_id',
        'type',
        'format',
        'text',
        'position',
        'raw_component',
        'example',
    ];

    protected $casts = [
        'raw_component' => 'array',
        'example' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function template(): BelongsTo
    {
        return $this->belongsTo(
            WhatsappCloudTemplate::class,
            'template_id'
        );
    }

    public function buttons(): HasMany
    {
        return $this->hasMany(
            WhatsappCloudTemplateButton::class,
            'component_id'
        )->orderBy('position');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isHeader(): bool
    {
        return $this->type === 'HEADER';
    }

    public function isBody(): bool
    {
        return $this->type === 'BODY';
    }

    public function isFooter(): bool
    {
        return $this->type === 'FOOTER';
    }

    public function isButtons(): bool
    {
        return $this->type === 'BUTTONS';
    }
}
