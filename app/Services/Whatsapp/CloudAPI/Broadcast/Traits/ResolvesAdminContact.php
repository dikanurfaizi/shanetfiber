<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast\Traits;

use App\Helpers\FormatReceiver;
use App\Models\Whatsapp\WhatsappNumber;

trait ResolvesAdminContact
{
    /**
     * Mengambil dan memformat kontak WhatsApp Admin default.
     * * @return array Array berisi 'contact_admin' dan 'contact_whatsapp'
     */
    protected function getAdminContact(): array
    {
        $formatter = new FormatReceiver;

        // Ambil data nomor default yang aktif sesuai kriteria Anda
        $whatsappNumberRecord = WhatsappNumber::where('is_enabled', true)
            ->where('is_default', true)
            ->whereHas('account', function ($query) {
                $query->where('is_active', 1)
                    ->whereHas('gateway', function ($q) {
                        $q->where('slug', 'gowa');
                    });
            })
            ->first();

        // Fallback jika tidak ditemukan di database
        $adminNumber = $whatsappNumberRecord ? $whatsappNumberRecord->number : '085783335333';

        return [
            'contact_admin'    => $formatter->formatReceiver($adminNumber, 'broadcast', false),
            'contact_whatsapp' => $formatter->formatReceiver($adminNumber, null, false),
        ];
    }
}
