<?php

namespace App\Services\Networks\Olt;

use App\Models\Network\OltUpdateLog;
use Illuminate\Support\Carbon;

class OltLogService
{
    /**
     * Simpan log hasil update atau scan OLT.
     */
    public static function insertLog(array $data): OltUpdateLog
    {
        return OltUpdateLog::create($data);
    }

    /**
     * Bersihkan log lama berdasarkan cutoff date (default: 1 hari).
     */
    public static function houseKeep(?int $days = 1): int
    {
        $cutoff = Carbon::now()->subDays($days);
        return OltUpdateLog::where('updated_at', '<', $cutoff)->delete();
    }

    /**
     * Ambil log terakhir untuk OLT tertentu.
     */
    public static function getLatestForOlt(int $oltId): ?OltUpdateLog
    {
        return OltUpdateLog::where('olt_id', $oltId)
            ->latest('updated_at')
            ->first();
    }
}
