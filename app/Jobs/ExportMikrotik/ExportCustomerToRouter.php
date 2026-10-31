<?php

namespace App\Jobs\ExportMikrotik;

use App\Events\UserRegisteredEvent;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Models\Websystem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportCustomerToRouter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika job gagal.
     */
    public int $tries = 3;

    /**
     * Waktu timeout job dalam detik (misal: 10 menit untuk batch besar).
     */
    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Mikrotik $mikrotik,
        public string $traceId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $timer = new ExecutionTimer();
        $message = '[ExportCustomerToRouter] Background customer export/sync';

        LogHelper::logInfo(null, [
            'mikrotik_id'   => $this->mikrotik->id,
            'mikrotik_name' => $this->mikrotik->name,
        ], 200, $message . " started", $this->traceId, $timer->lap());

        // Ambil konfigurasi driver provisioning sistem (radius, mikrotik, atau both)
        $webSystem = Websystem::first();
        $driver = $webSystem?->provisioning_driver ?? 'both';

        try {
            // Ambil data pelanggan aktif yang terikat ke MikroTik ini secara bertahap (chunk 100)
            // Eager load relasi penting untuk menghindari N+1 query problem saat dispatch Event
            Customer::with([
                'latestSubscription.package.pppProfile',
                'latestBilling.billingPayment.receipt'
            ])
                ->where('mikrotik_id', $this->mikrotik->id)
                ->where('is_active', true)
                ->chunk(100, function ($customers) use ($driver) {
                    foreach ($customers as $customer) {
                        $subscription = $customer->latestSubscription;
                        $billing      = $customer->latestBilling;
                        $receipt      = $billing?->billingPayment?->receipt;

                        // pppProfile otomatis ter-load via relasi Package (profile -> profile_name)
                        $pppProfile   = $subscription?->package?->pppProfile;

                        if ($subscription && $billing && $receipt) {
                            UserRegisteredEvent::dispatch(
                                $customer,
                                $pppProfile,
                                $customer->membership_id ?? null,
                                $subscription,
                                $billing,
                                $receipt,
                                null,
                                $this->traceId,
                                $driver
                            );
                        } else {
                            LogHelper::logWarning(null, [
                                'customer_id'   => $customer->id,
                                'customer_name' => $customer->name,
                                'has_sub'       => !is_null($subscription),
                                'has_billing'   => !is_null($billing),
                                'has_receipt'   => !is_null($receipt),
                            ], 400, "[ExportCustomerToRouter] Incomplete customer relations, skipped event dispatch", $this->traceId);
                        }
                    }
                });

            LogHelper::logInfo(null, [
                'mikrotik_id'   => $this->mikrotik->id,
                'mikrotik_name' => $this->mikrotik->name,
            ], 200, $message . " finished successfully", $this->traceId, $timer->lap());
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'mikrotik_id'   => $this->mikrotik->id,
                'error_message' => $e->getMessage(),
                'file'          => $e->getFile(),
                'line'          => $e->getLine(),
            ], 500, $message . " failed", $this->traceId, $timer->lap());

            // Melempar kembali exception agar Laravel Queue mencatat Kegagalan & Retry
            throw $e;
        }
    }
}
