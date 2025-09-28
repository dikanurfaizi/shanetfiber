<?php

namespace App\Helpers;

class SnmpHelper
{
    /**
     * Resolve hostname ke IP. Jika sudah IP, tetap return IP.
     * Jika gagal resolve, return null.
     */
    public static function resolveIp($host)
    {
        $resolved = gethostbyname($host);

        // kalau gagal resolve, gethostbyname() return host yang sama
        if ($resolved === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        return $resolved;
    }

    public static function formatMacFromSnmp($value)
    {
        // Hapus prefix STRING: dan tanda kutip
        $value = trim(str_replace(['STRING:', '"'], '', $value));

        // Jika panjangnya 12 karakter (misal E843687C35EA), ubah jadi MAC standar
        if (preg_match('/^[0-9A-Fa-f]{12}$/', $value)) {
            return strtoupper(implode(':', str_split($value, 2)));
        }

        // fallback: kembalikan apa adanya
        return $value;
    }

    public static function parseHexStringToAscii($hexString)
    {
        // Hilangkan prefix "Hex-STRING:" jika ada
        $hexString = preg_replace('/^Hex-STRING:\s*/', '', $hexString);

        // Pecah string menjadi array hex
        $hexArray = preg_split('/\s+/', trim($hexString));

        $ascii = '';
        foreach ($hexArray as $hex) {
            $intVal = hexdec($hex);

            // Hanya karakter printable, berhenti saat pertama kali non-printable
            if ($intVal >= 32 && $intVal <= 126) {
                $ascii .= chr($intVal);
            } else {
                break; // berhenti saat karakter aneh muncul
            }
        }

        return $ascii;
    }

    public static function extractLastThreeFromOid($fullIndex)
    {
        // Pecah OID menjadi array berdasarkan titik
        $parts = explode('.', $fullIndex);

        // Ambil 3 elemen terakhir
        $lastThree = array_slice($parts, -3);

        // Gabungkan lagi menjadi string
        return implode('.', $lastThree);
    }

    public static function mapSnmpStatus(string $snmpStatus): string
    {
        return match ($snmpStatus) {
            'Up' => 'Active',
            'Down' => 'Inactive',
            default => 'Scanning',
        };
    }
}
