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
            // hapus semua non-digit (termasuk spasi, strip, tanda plus, dll)
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

        return $contact ? '0' . substr($contact, 2) : '';
    }

    /**
     * Format broadcast
     * hasil: 0812-3456-789
     */
    public function broadcast(string $contact): string
    {
        $local = $this->display($contact);
        if (!$local) return '';

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

        return $contact ? $contact . '@s.whatsapp.net' : '';
    }

    /**
     * Format homepage dinamis untuk segala panjang nomor (termasuk 13-14 digit)
     */
    public function homepage(string $contact): string
    {
        $contact = $this->normalize($contact);
        if (!$contact) return '';

        $number = substr($contact, 2);
        $length = strlen($number);

        if ($length < 9) {
            return '+62 ' . $number;
        }

        if ($length >= 13) {
            $part1 = substr($number, 0, 3);
            $part2 = substr($number, 3, 4);
            $part3 = substr($number, 7, 4);
            $part4 = substr($number, 11);

            return "+62 $part1-$part2-$part3-$part4";
        }

        if ($length === 10) {
            $part1 = substr($number, 0, 3);
            $part2 = substr($number, 3, 3);
            $part3 = substr($number, 6);

            return "+62 $part1-$part2-$part3";
        }

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
     * 🌟 PERBAIKAN UTAMA: Normalisasi sender dari berbagai jenis payload data raw gateway
     */
    public function normalizeSender(string $raw): string
    {
        // 1. Ekstrak string jika mengandung data " in " (grup) atau JID pecahan ":"
        if (str_contains($raw, ' in ')) {
            $raw = explode(' in ', $raw)[0];
        }

        if (str_contains($raw, ':')) {
            $raw = explode(':', $raw)[0];
        }

        // 2. Buang suffix WhatsApp domain jika ada
        $cleanNumber = str_replace('@s.whatsapp.net', '', $raw);

        // 3. 🌟 PROTEKSI TAMBAHAN: Jalankan fungsi normalize() bawaan
        // Agar jika ada nomor berawalan '08xxx' atau '+62xxx' masuk ke method ini,
        // dia langsung dipaksa menjadi berformat canonical internasional '628xxx'
        $canonical = $this->normalize($cleanNumber);

        // Jika karena suatu alasan formatnya rusak total, kembalikan string pembersihan awal
        return $canonical ?? $cleanNumber;
    }
}
