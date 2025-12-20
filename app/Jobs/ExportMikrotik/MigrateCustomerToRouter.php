<?php

namespace App\Jobs\ExportMikrotik;

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

class MigrateCustomerToRouter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $sourceRouterId;
    protected int $targetRouterId;
    protected string $traceId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $sourceRouterId, int $targetRouterId, string $traceId)
    {
        $this->sourceRouterId = $sourceRouterId;
        $this->targetRouterId = $targetRouterId;
        $this->traceId = $traceId;
    }

    /**
     * Execute the job.
     */
    public function handle(SecretService $secretService): void
    {
        $startTime = microtime(true);
        $source = Mikrotik::findOrFail($this->sourceRouterId);
        $target = Mikrotik::findOrFail($this->targetRouterId);

        $stepStart = microtime(true);
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
            round((microtime(true) - $stepStart) * 1000, 2)
        );

        $customers = Customer::where('mikrotik_id', $source->id)->get();

        $stepStart = microtime(true);
        LogHelper::logInfo(
            null,
            [
                'total_customers_to_migrate' => $customers->count(),
            ],
            200,
            'Number of customers fetched for migration',
            $this->traceId,
            round((microtime(true) - $stepStart) * 1000, 2)
        );

        foreach ($customers as $customer) {
            $service = optional($customer->pppType)->name;
            $profile = optional($customer->package)->profile;

            $comment = optional(
                AutoIsolir::where('mikrotik_id', $target->id)->first()
            )->comment_unpayment ?? 'import_from_customer_management';

            $stepStart = microtime(true);
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
                round((microtime(true) - $stepStart) * 1000, 2)
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

                $stepStart = microtime(true);
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
                    round((microtime(true) - $stepStart) * 1000, 2)
                );
            } else {
                $stepStart = microtime(true);
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
                    round((microtime(true) - $stepStart) * 1000, 2)
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
            round((microtime(true) - $startTime) * 1000, 2)
        );
    }
}
