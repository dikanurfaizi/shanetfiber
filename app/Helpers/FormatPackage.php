<?php

namespace App\Helpers;

class FormatPackage
{
    public static function formatPackageName($packageName)
    {
        // Ubah pola (X Mbps) menjadi Speed X Mbps
        return preg_replace('/\((\d+)\s*Mbps\)/', 'Speed $1 Mbps', $packageName);
    }
}
