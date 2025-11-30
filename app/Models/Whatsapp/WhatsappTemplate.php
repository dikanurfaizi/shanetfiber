<?php

namespace App\Models\Whatsapp;

use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    protected $fillable = [
        'meta_waba_id',
        'phone_number_id',
        'meta_id',
        'name',
        'language',
        'category',
        'sub_category',
        'parameter_format',
        'status',
        'components',
        'examples',
        'placeholders_count',
        'has_header',
        'has_body',
        'has_footer',
        'has_buttons',
        'synced_at',
    ];

    protected $casts = [
        'components' => 'array',
        'examples' => 'array',
        'has_header' => 'boolean',
        'has_body' => 'boolean',
        'has_footer' => 'boolean',
        'has_buttons' => 'boolean',
        'synced_at' => 'datetime',
    ];

    /**
     * Hitung jumlah placeholder {{n}}
     */
    public function countPlaceholders(): int
    {
        $body = json_encode($this->components);
        preg_match_all('/{{\d+}}/', $body, $matches);

        return count($matches[0]);
    }
}
