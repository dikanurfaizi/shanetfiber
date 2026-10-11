<?php

namespace App\Models\WhatsApp\Cloud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppCloudTemplate extends Model
{
    protected $table = 'whatsapp_cloud_templates';

    protected $fillable = [
        'waba_template_id',
        'account_id',
        'name',
        'language',
        'category',
        'sub_category',
        'status',
        'parameter_format',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function components(): HasMany
    {
        return $this->hasMany(
            WhatsAppCloudTemplateComponent::class,
            'template_id'
        )->orderBy('position');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isApproved(): bool
    {
        return strtoupper($this->status) === 'APPROVED';
    }
}
