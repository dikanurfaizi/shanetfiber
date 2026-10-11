<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsAppFeature extends Model
{
    protected $table = 'whatsapp_features';

    protected $guarded = ['id'];

    protected $casts = [
        'schedule' => 'array',
        'is_active' => 'boolean',
        'is_scheduled' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Mapping gateway berdasarkan type feature
     */
    public function getGatewayLabelAttribute(): string
    {
        return match ($this->type) {
            'onprem' => 'Gowa',
            'cloud'  => 'Meta API',
            default  => 'Unknown',
        };
    }

    /**
     * Mendapatkan ringkasan jadwal pengiriman fitur WhatsApp
     */

    public function getScheduleSummaryAttribute(): string
    {
        if (!$this->is_scheduled || empty($this->schedule)) {
            return 'Not Scheduled';
        }

        $days = $this->schedule['days'] ?? [];
        $timezone = $this->schedule['timezone'] ?? 'Asia/Jakarta';
        $window = $this->schedule['time_window'] ?? null;

        if (empty($days)) {
            return 'Scheduled (no days configured)';
        }

        sort($days);

        $dayLabel = implode(', ', $days);

        if ($window && isset($window['start'], $window['end'])) {
            return sprintf(
                'Every %s at %s-%s (%s)',
                $dayLabel,
                $window['start'],
                $window['end'],
                $timezone
            );
        }

        return sprintf(
            'Every %s (%s)',
            $dayLabel,
            $timezone
        );
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->alias) && !empty($model->name)) {
                $model->alias = Str::lower(Str::slug($model->name, '_'));
            }
        });
    }
}
