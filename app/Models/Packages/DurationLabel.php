<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Model;

class DurationLabel extends Model
{
    protected $fillable = [
        'slug',
        'duration_months',
        'name_en',
        'name_id',
    ];

    public function packageDurations()
    {
        return $this->hasMany(PackageDuration::class, 'duration_id');
    }
}
