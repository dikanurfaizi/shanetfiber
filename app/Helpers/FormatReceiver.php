<?php

namespace App\Helpers;

use App\Helpers\LogHelper;

class FormatReceiver
{
    public function formatReceiver($contact, $action = null)
    {
        try {
            // Hapus karakter non-digit, kecuali tanda "+" di awal
            $contact = preg_replace('/[^\d+]/', '', $contact);

            // Jika nomor diawali dengan "+62", ubah menjadi "62"
            if (str_starts_with($contact, '+62')) {
                $contact = substr($contact, 1);
            }

            if ($action === 'broadcast') {
                // Jika nomor dimulai dengan "08", ubah ke format "62"
                if (str_starts_with($contact, '08')) {
                    $contact = '62' . substr($contact, 1);
                }

                // Ambil digit setelah prefix 62
                if (str_starts_with($contact, '62')) {
                    $numberOnly = substr($contact, 2);

                    // Jika 9 digit → pakai +62
                    if (strlen($numberOnly) === 9) {
                        $contact = '+62' . $numberOnly;
                    } else {
                        // Tetap pakai "62" (tanpa plus)
                        $contact = '62' . $numberOnly;
                    }
                }
            } else {
                // Untuk tampilan biasa (non-broadcast)
                $contact = preg_replace('/^628/', '08', $contact);
                $contact = preg_replace('/^(\d{4})(\d{4})(\d+)$/', '$1-$2-$3', $contact);
            }

            return $contact;
        } catch (\Exception $e) {
            LogHelper::logWarning(
                null,
                null,
                500,
                "Error manipulating contact bill info for contact: $contact | Action: $action | " . $e->getMessage()
            );
            return null;
        }
    }
}
