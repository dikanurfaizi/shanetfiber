<?php

namespace App\Services\Networks;

use Illuminate\Support\Carbon;
use App\Models\Network\OltUpdateLog;

class HouseKeepingLog
{
    public static function houseKeepingLog(): int
    {
        $cutoff = Carbon::now()->subDay(); // 1 hari ke belakang
        return OltUpdateLog::where('updated_at', '<', $cutoff)->delete();
    }
}
