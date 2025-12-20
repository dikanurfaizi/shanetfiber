<?php

namespace App\Services\Messaging\Handlers;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;

abstract class BaseBroadcastAwareHandler
{
    protected string $category;
    protected ?string $rawContact = null; // angka asli, belum diformat

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function setRawContact(?string $rawContact): void
    {
        $this->rawContact = $rawContact;
    }

    /**
     * Jalankan semua validasi standar:
     * - contact kosong
     * - normalisasi nomor
     * - ambil customer
     * - fail-safe kategori BROADCAST + opt-out
     */
    protected function validateBase(string $traceId): ?Customer
    {
        // 1. Contact kosong
        if (empty($this->rawContact)) {
            LogHelper::logError(null, [
                'rawContact' => $this->rawContact
            ], 400, 'Contact kosong di BaseBroadcastAwareHandler', $traceId);

            throw new \RuntimeException('Contact kosong');
        }

        // 2. Format nomor
        $format = new FormatReceiver();
        $contact1 = $format->formatReceiver($this->rawContact, null, false);
        $contact2 = $format->formatReceiver($this->rawContact, 'display', false);

        // 3. Cari customer
        $customer = Customer::where('contact', $contact1)
            ->orWhere('contact', $contact2)
            ->first();

        // 4. Fail-safe: jika kategori BROADCAST → cek opt-out
        if ($this->category === 'BROADCAST' && ($customer->is_opt_out ?? 0) == 1) {
            LogHelper::logWarning(
                null,
                [
                    'contact'      => $this->rawContact,
                    'customer_id'  => $customer->id ?? null,
                ],
                403,
                'Customer opted-out. Broadcast blocked.',
                $traceId
            );

            throw new \RuntimeException('Customer opted-out');
        }

        return $customer;
    }
}
