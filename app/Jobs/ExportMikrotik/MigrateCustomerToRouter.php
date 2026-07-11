<?php

namespace App\Jobs\ExportMikrotik;

use App\Helpers\ExecutionTimer;
use App\Models\Customers\Customer;
use App\Models\AutoIsolir;
use App\Models\Mikrotik;
use App\Services\Routerboard\Ppp\SecretService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\LogHelper;
use App\Models\Customers\CustomerSubscription;

class MigrateCustomerToRouter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Ubah tipe data properti dari int menjadi model Mikrotik
    protected Mikrotik $sourceRouter;
    protected Mikrotik $targetRouter;
    protected string $traceId;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $sourceRouter, Mikrotik $targetRouter, string $traceId)
    {
        $this->sourceRouter = $sourceRouter;
        $this->targetRouter = $targetRouter;
        $this->traceId = $traceId;
    }

    /**
     * Execute the job.
     */
    public function handle(SecretService $secretService): void
    {
        $timer = new ExecutionTimer();

        // Dua baris query findOrFail() di sini SUDAH DIHAPUS karena Laravel otomatis meload modelnya
        $source = $this->sourceRouter;
        $target = $this->targetRouter;

        LogHelper::logInfo(
            null,
            [
                'source_router_id' => $source->id,
                'source_router_name' => $source->name,
                'target_router_id' => $target->id,
                'target_router_name' => $target->name,
            ],
            200,
            'Starting customer migration from source to target router',
            $this->traceId,
            $timer->lap()
        );

        $customers = Customer::where('mikrotik_id', $source->id)->get();

        LogHelper::logInfo(
            null,
            [
                'total_customers_to_migrate' => $customers->count(),
            ],
            200,
            'Number of customers fetched for migration',
            $this->traceId,
            $timer->lap()
        );

        // N+1 Query Fix: Ambil data isolir sekali saja di luar perulangan agar tidak query berulang kali di dalam foreach
        $autoIsolirComment = optional(
            AutoIsolir::where('mikrotik_id', $target->id)->first()
        )->comment_unpayment ?? 'import_from_customer_management';

        foreach ($customers as $customer) {
            $service = optional($customer->pppType)->name;
            $profile = $customer->active_package->profile ?? null;
            $comment = $autoIsolirComment;

            LogHelper::logInfo(
                null,
                [
                    'source_router' => $source->name,
                    'target_router' => $target->name,
                    'username' => $customer->username,
                    'service' => $service,
                    'profile' => $profile,
                    'comment' => $comment,
                ],
                200,
                'Migrating customer from source to target router',
                $this->traceId,
                $timer->lap()
            );

            $response = $secretService->createSecret(
                $target,
                $customer->username,
                $customer->password_ppp,
                $service,
                $profile,
                $comment,
                $this->traceId
            );

            if (!isset($response['after']['message'])) {
                $customer->update([
                    'secret_id' => $response['after']['ret'],
                    'mikrotik_id' => $target->id,
                ]);

                LogHelper::logInfo(
                    null,
                    [
                        'username' => $customer->username,
                        'new_router' => $target->name,
                        'secret_id' => $response['after']['ret'],
                    ],
                    200,
                    'Customer migrated successfully',
                    $this->traceId,
                    $timer->lap()
                );
            } else {
                LogHelper::logError(
                    null,
                    [
                        'source_router' => $source->name,
                        'target_router' => $target->name,
                        'customer' => $customer->username,
                        'error' => $response['after']['message'] ?? 'Unknown error'
                    ],
                    500,
                    'Failed to migrate customer',
                    $this->traceId,
                    $timer->lap()
                );

                $customer->update(['secret_id' => 0]);
            }
        }

        LogHelper::logInfo(
            null,
            [
                'source_router' => $source->name,
                'target_router' => $target->name,
            ],
            200,
            'Customer migration process finished',
            $this->traceId,
            $timer->lap()
        );
    }
}
