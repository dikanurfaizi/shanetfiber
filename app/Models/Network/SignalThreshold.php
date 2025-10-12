<?php

namespace App\Models\Network;

use Illuminate\Database\Eloquent\Model;

class SignalThreshold extends Model
{
    protected $table = 'signal_thresholds';

    protected $fillable = [
        'name',
        'label',
        'min_value',
        'max_value',
        'description',
    ];

    /**
     * Mencari threshold yang cocok berdasarkan nilai Rx Power.
     */
    public static function matchByPower($rxPower)
    {
        return self::where('min_value', '<=', $rxPower)
            ->where('max_value', '>=', $rxPower)
            ->first();
    }

    /**
     * Scope: urutkan berdasarkan kekuatan sinyal (max_value terbesar ke terkecil)
     * Jika ada NULL (misal N/A), taruh di urutan terakhir.
     */
    public function scopeSorted($query)
    {
        return $query
            ->orderByRaw('ISNULL(max_value), max_value DESC');
    }
}
