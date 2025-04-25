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

    //protected Mikrotik $source;
    protected Mikrotik $target;

    /**
     * Create a new job instance.
     */
    // public function __construct(Mikrotik $source, Mikrotik $target)
    public function __construct(Mikrotik $target)
    {
        // $this->source = $source;
        $this->target = $target;
    }

    /**
     * Execute the job.
     */
    public function handle(SecretService $secretService): void
    {
        $customers = Customer::where('mikrotik_id', $this->target->id)->get();

        LogHelper::logWarning(null, ['target_mikrotik' => $this->target], 200, 'Target Mikrotik');

        try {
            $existingProfiles = collect($secretService->getPppProfile($this->target))->pluck('name')->toArray();
            LogHelper::logInfo(null, [
                'router' => $this->target->name,
                'existing_profiles' => $existingProfiles,
            ], 200, 'Available PPP Profiles in Target Router');
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'router' => $this->target->name,
                'error' => $e->getMessage(),
            ], 500, 'Failed to get PPP profiles from target router');
            return;
        }

        foreach ($customers as $customer) {
            $service = optional($customer->pppType)->name;
            $profile = optional($customer->paket)->profile;

            // Skip kalau profil customer tidak ada di Mikrotik target
            if (!in_array($profile, $existingProfiles)) {
                LogHelper::logInfo(null, [
                    'username' => $customer->username,
                    'profile' => $profile,
                    'router' => $this->target->name,
                ], 200, 'Skipped customer migration: profile not found in target router');
                continue;
            }

            $comment = optional(
                AutoIsolir::where('mikrotik_id', $this->target->id)->first()
            )->comment_unpayment ?? 'import_from_customer_management';

            LogHelper::logInfo(null, [
                'target_router' => $this->target->name,
                'username' => $customer->username,
                'service' => $service,
                'profile' => $profile,
                'comment' => $comment,
            ], 200, 'Migrating customer to target router');

            $response = $secretService->createSecret(
                $this->target,
                $customer->username,
                $customer->password_ppp,
                $service,
                $profile,
                $comment
            );

            if (!isset($response['after']['message'])) {
                $customer->update([
                    'secret_id' => $response['after']['ret'],
                    'mikrotik_id' => $this->target->id,
                ]);
            } else {
                LogHelper::logError(null, [
                    'router' => $this->target->name,
                    'customer' => $customer->username,
                    'error' => $response['after']['message'] ?? 'Unknown error'
                ], 500, 'Failed to migrate customer');
                $customer->update(['secret_id' => 0]);
            }
        }
    }
}
