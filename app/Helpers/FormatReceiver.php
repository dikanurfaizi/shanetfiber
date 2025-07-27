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
                if (strlen($numberOnly) <= 8) {
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
}
