<?php

namespace App\Helpers;

use App\Helpers\LogHelper;

class FormatReceiver
{
    public function formatReceiver($contact, $action = null, $whatsappFormat = false)
    {
        try {
            // Bersihkan semua kecuali angka
            $contact = preg_replace('/[^\d]/', '', $contact);

            // Konversi ke 62xxx
            if (str_starts_with($contact, '08')) {
                $contact = '62' . substr($contact, 1);
            } elseif (str_starts_with($contact, '620')) {
                $contact = '62' . ltrim(substr($contact, 2), '0');
            } elseif (str_starts_with($contact, '+62')) {
                $contact = '62' . substr($contact, 3);
            }

            // WhatsApp format
            if ($whatsappFormat) {
                $numberOnly = substr($contact, 2); // hapus 62
                if (strlen($numberOnly) <= 9) {
                    $contact = '+62' . $numberOnly;
                }
                return preg_replace('/@s\.whatsapp\.net$/', '', $contact) . '@s.whatsapp.net';
            }

            // Format untuk broadcast: pisah dengan dash
            if ($action === 'broadcast') {
                // Ubah ke format lokal dulu: 08xxxxxxxxx
                $local = '0' . substr($contact, 2);
                return substr($local, 0, 4) . '-' . substr($local, 4, 4) . '-' . substr($local, 8);
            }

            // Format untuk display: kembalikan ke format lokal 08xxxxxxxxx
            if ($action === 'display') {
                return '0' . substr($contact, 2);
            }

            // Format untuk homepage: +62 xx-xxx-xxx-xx
            if ($action === 'homepage') {
                $number = substr($contact, 2); // ambil sisanya tanpa 62

                // pastikan panjang minimal agar substr tidak error
                if (strlen($number) < 10) {
                    return '+62 ' . $number; // fallback simple
                }

                // Pisah sesuai pola: 2-3-3-2 digit
                $part1 = substr($number, 0, 2);
                $part2 = substr($number, 2, 3);
                $part3 = substr($number, 5, 3);
                $part4 = substr($number, 8, 2);

                return '+62 ' . $part1 . '-' . $part2 . '-' . $part3 . '-' . $part4;
            }

            // Default: balikin 62xxxxxxxxx
            return $contact;
        } catch (\Exception $e) {
            LogHelper::logWarning(
                null,
                null,
                500,
                "Error formatting contact: $contact | Action: $action | " . $e->getMessage()
            );
            return null;
        }
    }

    public function normalizeSender(string $raw): string
    {
        if (str_contains($raw, ' in ')) {
            $raw = explode(' in ', $raw)[0];
        }

        if (str_contains($raw, ':')) {
            $raw = explode(':', $raw)[0];
        }

        return str_replace('@s.whatsapp.net', '', $raw);
    }
}
