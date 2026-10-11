<?php

namespace App\Services\Messaging\Handlers;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;

abstract class BaseBroadcastAwareHandler
{
    protected string $category = 'BROADCAST';
    protected ?string $rawContact = null; // Nomor asli sebelum diformat

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function setRawContact(?string $rawContact): void
    {
        $this->rawContact = $rawContact;
    }

    /**
     * Jalankan validasi standar untuk pesan berbasis broadcast:
     * 1. Validasi kontak tidak boleh kosong
     * 2. Normalisasi nomor penerima
     * 3. Ambil data Customer berdasarkan kontak
     * 4. Fail-safe: Blokir pengiriman jika kategori BROADCAST & Customer Opt-Out
     */
    protected function validateBase(string $traceId): ?Customer
    {
        // 1. Validasi Contact Kosong
        if (empty(trim((string) $this->rawContact))) {
            LogHelper::logError(
                null,
                ['rawContact' => $this->rawContact],
                400,
                '[BaseBroadcastAwareHandler] Target contact number is empty',
                $traceId
            );

            throw new \RuntimeException('Target contact number cannot be empty.');
        }

        // 2. Normalisasi & Format Nomor HP
        /** @var FormatReceiver $format */
        $format   = app(FormatReceiver::class);
        $contact1 = $format->formatReceiver($this->rawContact, null, false);
        $contact2 = $format->formatReceiver($this->rawContact, 'display', false);

        // 3. Cari Data Customer
        $customer = Customer::query()
            ->where('contact', $contact1)
            ->orWhere('contact', $contact2)
            ->first();

        // 4. Fail-safe: Cek Status Opt-out jika kategori BROADCAST
        if ($customer && strtoupper($this->category) === 'BROADCAST') {
            // Menggunakan boolean strict check sesuai cast model Customer
            if ((bool) $customer->is_opt_out === true) {
                LogHelper::logWarning(
                    null,
                    [
                        'raw_contact' => $this->rawContact,
                        'customer_id' => $customer->id,
                        'category'    => $this->category,
                    ],
                    403,
                    '[BaseBroadcastAwareHandler] Customer opted-out. Broadcast blocked safely.',
                    $traceId
                );

                throw new \RuntimeException("Broadcast blocked: Customer ID {$customer->id} has opted-out.");
            }
        }

        return $customer;
    }
}
