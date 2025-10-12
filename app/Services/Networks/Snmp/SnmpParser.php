<?php

namespace App\Services\Networks\Snmp;

use App\Helpers\SnmpHelper;

class SnmpParser
{
    public static function extractIndex(string $fullOid, string $baseOid): string
    {
        $base = ltrim($baseOid, '.');
        $full = ltrim($fullOid, '.');
        $prefix = $base . '.';
        if (str_starts_with($full, $prefix)) {
            return substr($full, strlen($prefix));
        }
        $parts = explode('.', $full);
        return implode('.', array_slice($parts, -3));
    }

    public static function normalizeValue($val): string
    {
        if (!is_string($val)) return $val;

        // Example: Hex-STRING: 54 65 73 74
        if (preg_match('/Hex-STRING:\s*([0-9A-Fa-f\s]+)/', $val, $m)) {
            $hex = preg_replace('/\s+/', '', $m[1]);
            $hex2 = strtoupper(implode(':', str_split($hex, 2)));
            return trim(SnmpHelper::parseHexStringToAscii($hex2));
        }

        // Example: STRING: "Tamat" → Tamat
        if (preg_match('/^[A-Z\-]+:\s*"?([^"]*)"?$/', $val, $m2)) {
            return trim($m2[1]);
        }

        // Example: "STRING: Anis Nurfatimah" → Anis Nurfatimah
        if (str_starts_with($val, 'STRING:')) {
            $clean = trim(substr($val, 7));
            return trim($clean, "\" \t\n\r\0\x0B");
        }

        return trim($val);
    }

    public static function extractPonNumber(string $index): ?string
    {
        $parts = explode('.', $index);
        // fallback: kalau panjang kurang dari 2, ambil terakhir
        return $parts[1] ?? end($parts);
    }
}
