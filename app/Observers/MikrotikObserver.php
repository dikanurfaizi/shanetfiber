<?php

namespace App\Observers;

use App\Models\Mikrotik;
use App\Services\Radius\RadiusUserService;

class MikrotikObserver
{

    public function __construct(
        protected RadiusUserService $radiusUserService
    ) {}

    /**
     * Handle the Mikrotik "created" event.
     */
    public function created(Mikrotik $mikrotik): void
    {
        $this->radiusUserService->syncNas($mikrotik);
    }

    /**
     * Handle the Mikrotik "updated" event.
     */
    public function updated(Mikrotik $mikrotik): void
    {
        // Daftar atribut yang mempengaruhi tabel 'nas' FreeRADIUS
        $relevantAttributes = [
            'host',
            'name',
            'slug',
            'radius_secret',
            'disabled',
            'merk_router',
        ];

        // Cek apakah salah satu dari atribut di atas mengalami perubahan
        if ($mikrotik->wasChanged($relevantAttributes)) {
            $this->radiusUserService->syncNas($mikrotik);
        }
    }

    /**
     * Handle the Mikrotik "deleted" event.
     */
    public function deleted(Mikrotik $mikrotik): void
    {
        // Paksakan set disabled = 'true' secara memori lalu sync agar record di tabel 'nas' terhapus
        $mikrotik->disabled = 'true';
        $this->radiusUserService->syncNas($mikrotik);
    }

    /**
     * Handle the Mikrotik "restored" event.
     */
    public function restored(Mikrotik $mikrotik): void
    {
        //
    }

    /**
     * Handle the Mikrotik "force deleted" event.
     */
    public function forceDeleted(Mikrotik $mikrotik): void
    {
        //
    }
}
