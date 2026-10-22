<?php

namespace App\Services\Radius;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerProvisioningState;

class RadiusProvisioningStateService
{
    /**
     * Update atau buat record status provisioning pelanggan (RADIUS / Mikrotik)
     *
     * @param int $customerId
     * @param string $target 'radius' atau 'mikrotik'
     * @param string $status 'synced', 'failed', 'pending', 'out_of_sync'
     * @param string|null $errorMessage
     * @return CustomerProvisioningState
     */
    public function updateProvisioningState(
        int $customerId,
        string $target,
        string $status,
        ?string $errorMessage = null
    ): CustomerProvisioningState {
        $state = CustomerProvisioningState::firstOrNew(['customer_id' => $customerId]);

        // Jika record baru dibuat, inisialisasi awal status MikroTik berdasarkan aktivasi customer
        if (!$state->exists) {
            $customer = Customer::find($customerId);

            if ($customer && ($customer->activation || $customer->is_active) && $customer->activation_date) {
                $state->in_mikrotik = true;
                $state->mikrotik_status = 'synced';
                $state->last_mikrotik_sync_at = $customer->activation_date;
            } else {
                $state->in_mikrotik = false;
                $state->mikrotik_status = 'pending';
            }
        }

        // Update status sesuai target aktual (RADIUS atau MikroTik)
        if ($target === 'radius') {
            $state->radius_status = $status;
            $state->in_radius = ($status === 'synced');
            $state->last_radius_sync_at = now();
        } elseif ($target === 'mikrotik') {
            $state->mikrotik_status = $status;
            $state->in_mikrotik = ($status === 'synced');
            $state->last_mikrotik_sync_at = now();
        }

        // Penanganan error message
        if ($errorMessage !== null) {
            $state->last_error_message = $errorMessage;
        } elseif ($status === 'synced') {
            // Bersihkan error message lama jika sync terbaru berhasil
            $state->last_error_message = null;
        }

        $state->save();

        return $state;
    }
}
