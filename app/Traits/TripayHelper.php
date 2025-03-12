<?php

namespace App\Traits;

use App\Models\Tripay;
use Illuminate\Support\Facades\Cache;

trait TripayHelper
{
    public function getTripayData()
    {
        return Cache::remember('tripay_config', now()->addMinutes(10), function () {
            return Tripay::first();
        });
    }
}
