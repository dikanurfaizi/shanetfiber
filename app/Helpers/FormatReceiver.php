<?php

namespace App\Helpers;

use App\Helpers\LogHelper;

class FormatReceiver
{
    /**
     * Normalisasi nomor ke canonical format
     * hasil: 628xxxxxxxxx
     */
    public function normalize(?string $contact): ?string
    {
        if (!$contact) {
            return null;
        }

        try {

            // hapus semua non digit
            $contact = preg_replace('/\D/', '', $contact);

            if (!$contact) {
                return null;
            }

            // 08xxxx -> 628xxxx
            if (str_starts_with($contact, '08')) {
                $contact = '62' . substr($contact, 1);
            }

            // 8xxxx -> 628xxxx
            elseif (str_starts_with($contact, '8')) {
                $contact = '62' . $contact;
            }

            // 6208xxxx -> 628xxxx
            elseif (str_starts_with($contact, '620')) {
                $contact = '62' . ltrim(substr($contact, 2), '0');
            }

            // jika bukan 62 maka dianggap invalid
            elseif (!str_starts_with($contact, '62')) {
                return null;
            }

            return $contact;
        } catch (\Throwable $e) {

            LogHelper::logWarning(
                null,
                null,
                500,
                "Error normalizing contact: $contact | " . $e->getMessage()
            );

            return null;
        }
    }

    /**
     * Format untuk UI display
     * hasil: 08xxxxxxxxx
     */
    public function display(string $contact): string
    {
        $contact = $this->normalize($contact);

        return '0' . substr($contact, 2);
    }

    /**
     * Format broadcast
     * hasil: 0812-3456-789
     */
    public function broadcast(string $contact): string
    {
        $local = $this->display($contact);

        return substr($local, 0, 4)
            . '-' . substr($local, 4, 4)
            . '-' . substr($local, 8);
    }

    /**
     * Format WhatsApp gateway
     * hasil: 628123456789@s.whatsapp.net
     */
    public function whatsapp(string $contact): string
    {
        $contact = $this->normalize($contact);

        return $contact . '@s.whatsapp.net';
    }

    /**
     * Format homepage dinamis untuk segala panjang nomor (termasuk 13-14 digit)
     * Contoh hasil: 
     * - 10 digit: +62 812-345-6789
     * - 11-12 digit: +62 812-3456-7890
     * - 13-14 digit: +62 812-3456-7890-1234
     */
    public function homepage(string $contact): string
    {
        // 1. Bersihkan nomor (misal hasil: 628123456789012)
        $contact = $this->normalize($contact);

        // 2. Ambil nomor setelah "62" (misal hasil: 8123456789012)
        $number = substr($contact, 2);
        $length = strlen($number);

        if ($length < 9) {
            return '+62 ' . $number;
        }

        // 3. Jika nomor super panjang (13 digit atau lebih)
        if ($length >= 13) {
            // Pola: 3-4-4-Sisa (Maksimal 4 digit di setiap grup)
            $part1 = substr($number, 0, 3);
            $part2 = substr($number, 3, 4);
            $part3 = substr($number, 7, 4);
            $part4 = substr($number, 11); // Mengambil semua sisa angka di belakang

            return "+62 $part1-$part2-$part3-$part4";
        }

        // 4. Jika nomor standar pendek (10 digit)
        if ($length === 10) {
            $part1 = substr($number, 0, 3);
            $part2 = substr($number, 3, 3);
            $part3 = substr($number, 6);

            return "+62 $part1-$part2-$part3";
        }

        // 5. Jika nomor standar umum (11 - 12 digit)
        $part1 = substr($number, 0, 3);
        $part2 = substr($number, 3, 4);
        $part3 = substr($number, 7);

        return "+62 $part1-$part2-$part3";
    }

    /**
     * Wrapper agar kompatibel dengan helper lama
     */
    public function formatReceiver($contact, $action = null, $whatsappFormat = false)
    {
        try {

            if ($whatsappFormat) {
                return $this->whatsapp($contact);
            }

            if ($action === 'broadcast') {
                return $this->broadcast($contact);
            }

            if ($action === 'display') {
                return $this->display($contact);
            }

            if ($action === 'homepage') {
                return $this->homepage($contact);
            }

            return $this->normalize($contact);
        } catch (\Throwable $e) {

            LogHelper::logWarning(
                null,
                null,
                500,
                "Error formatting contact: $contact | Action: $action | " . $e->getMessage()
            );

            return null;
        }
    }

    /**
     * Normalisasi sender dari WhatsApp gateway
     */
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
