<?php

namespace App\Jobs;

use App\Models\Customer;
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

    protected Mikrotik $source;
    protected Mikrotik $target;
    private string $traceId;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $source, Mikrotik $target, string $traceId)
    {
        $this->source = $source;
        $this->target = $target;
        $this->traceId = $traceId;
    }

    /**
     * Execute the job.
     */
    public function handle(SecretService $secretService): void
    {
        LogHelper::logInfo(null, [
            'source_router_id' => $this->source->id,
            'source_router_name' => $this->source->name,
            'target_router_id' => $this->target->id,
            'target_router_name' => $this->target->name,
        ], 200, 'Starting customer migration from source to target router');

        $customers = Customer::where('mikrotik_id', $this->source->id)->get();

        LogHelper::logInfo(null, [
            'total_customers_to_migrate' => $customers->count(),
        ], 200, 'Number of customers fetched for migration');

        foreach ($customers as $customer) {
            $service = optional($customer->pppType)->name;
            $profile = optional($customer->paket)->profile;

            $comment = optional(
                AutoIsolir::where('mikrotik_id', $this->target->id)->first()
            )->comment_unpayment ?? 'import_from_customer_management';

            LogHelper::logInfo(null, [
                'source_router' => $this->source->name,
                'target_router' => $this->target->name,
                'username' => $customer->username,
                'service' => $service,
                'profile' => $profile,
                'comment' => $comment,
            ], 200, 'Migrating customer from source to target router');

            $response = $secretService->createSecret(
                $this->target,
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
                    'mikrotik_id' => $this->target->id,
                ]);

                LogHelper::logInfo(null, [
                    'username' => $customer->username,
                    'new_router' => $this->target->name,
                    'secret_id' => $response['after']['ret'],
                ], 200, 'Customer migrated successfully');
            } else {
                LogHelper::logError(null, [
                    'source_router' => $this->source->name,
                    'target_router' => $this->target->name,
                    'customer' => $customer->username,
                    'error' => $response['after']['message'] ?? 'Unknown error'
                ], 500, 'Failed to migrate customer');

                $customer->update(['secret_id' => 0]);
            }
        }

        LogHelper::logInfo(null, [
            'source_router' => $this->source->name,
            'target_router' => $this->target->name,
        ], 200, 'Customer migration process finished');
    }
}
