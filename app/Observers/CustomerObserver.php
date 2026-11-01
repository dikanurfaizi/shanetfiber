<?php

namespace App\Observers;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerLocationHistory;
use Illuminate\Support\Facades\Auth;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        // Simpan history awal jika koordinat diisi saat pembuatan customer
        if (!is_null($customer->latitude) && !is_null($customer->longitude)) {
            $this->recordHistory($customer);
        }
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        // Hanya simpan history jika kolom latitude ATAU longitude mengalami perubahan nilai
        if ($customer->isDirty('latitude') || $customer->isDirty('longitude')) {
            $this->recordHistory($customer);
        }
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        //
    }

    /**
     * Helper privat untuk menyimpan log riwayat lokasi
     */
    private function recordHistory(Customer $customer): void
    {
        CustomerLocationHistory::create([
            'customer_id' => $customer->id,
            'latitude'    => $customer->latitude,
            'longitude'   => $customer->longitude,
            'address'     => $customer->address,
            'created_by'  => Auth::id(), // Otomatis mencatat ID user/admin yang login
        ]);
    }
}
