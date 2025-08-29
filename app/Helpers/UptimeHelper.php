<?php

namespace App\Helpers;

class UptimeHelper
{
    public static function format(string $uptime): string
    {
        // contoh input: "1d17h5m24s" atau "17h5m24s"
        preg_match('/((?P<days>\d+)d)?((?P<hours>\d+)h)?((?P<minutes>\d+)m)?((?P<seconds>\d+)s)?/', $uptime, $matches);

        $days    = isset($matches['days']) ? (int) $matches['days'] : 0;
        $hours   = isset($matches['hours']) ? (int) $matches['hours'] : 0;
        $minutes = isset($matches['minutes']) ? (int) $matches['minutes'] : 0;
        $seconds = isset($matches['seconds']) ? (int) $matches['seconds'] : 0;

        // format singkat: 1d 17h 5m 24s
        $parts = [];
        if ($days) $parts[] = $days . 'd';
        if ($hours) $parts[] = $hours . 'h';
        if ($minutes) $parts[] = $minutes . 'm';
        if ($seconds) $parts[] = $seconds . 's';

        return implode(' ', $parts);
    }
}
