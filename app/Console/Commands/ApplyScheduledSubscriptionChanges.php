<?php

namespace App\Console\Commands;

use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Customers\SubscriptionChange;
use App\Services\Customers\Provisioning\ChangePackageService;
use App\Services\Customers\Subscription\SubscriptionResolver;
use DomainException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ApplyScheduledSubscriptionChanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:apply-scheduled-subscription-changes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $traceId = 'cmd-' . now()->format('YmdHis') . '-' . bin2hex(random_bytes(4));
        $timer   = new ExecutionTimer();
        $changes = SubscriptionChange::scheduled()
            ->effective()
            ->where('change_type', 'downgrade')
            ->orderBy('effective_at')
            ->get();

        foreach ($changes as $change) {
            $this->applyChangeSafely($change, $traceId, $timer);
        }

        return self::SUCCESS;
    }

    private function applyChangeSafely(SubscriptionChange $change, string $traceId, ExecutionTimer $timer): void
    {
        DB::beginTransaction();

        try {
            $this->applyChange($change, $traceId, $timer);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            $change->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            LogHelper::logError(
                null,
                [
                    'subscription_change_id' => $change->id,
                    'customer_id'            => $change->customer_id,
                    'error'                  => [
                        'code'    => $e->getCode(),
                        'message' => $e->getMessage(),
                    ]
                ],
                500,
                'Failed to apply scheduled subscription change',
                $traceId,
                $timer->lap()
            );
        }
    }

    private function applyChange(SubscriptionChange $change, string $traceId, ExecutionTimer $timer): void
    {
        $customer = $change->customer;

        if (!$customer) {
            throw new \DomainException('Customer not found');
        }

        /**
         * ======================================================
         * 1. Resolve active subscription (normalize)
         * ======================================================
         */
        $activeSub = CustomerSubscription::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        if (!$activeSub) {
            throw new DomainException('No active subscription found for customer');
        }

        /**
         * Optional guard:
         * pastikan old_subscription_id (kalau ada) match
         */
        if (
            $change->old_subscription_id &&
            (int) $change->old_subscription_id !== (int) $activeSub->id
        ) {
            throw new DomainException(
                'Active subscription does not match scheduled downgrade source'
            );
        }

        /**
         * ======================================================
         * 2. Terminate active subscription
         * ======================================================
         */
        $activeSub->update([
            'status'        => 'terminated',
            'end_date'      => now(),
            'terminated_at' => now(),
        ]);

        $activeSub->refresh();

        /**
         * ======================================================
         * 3. Create new subscription (downgrade)
         * ======================================================
         */
        $subscriptionResolver = app()->make(SubscriptionResolver::class);
        $periodStart = now()->startOfDay();
        $periodEnd   = now()->endOfMonth();

        $data = [
            'package_id'          => $change->new_package_id,
            'package_duration_id' => $change->new_package_duration_id,
        ];

        $activeSubForCompare = $subscriptionResolver->resolveOldSubscriptionAndNormalize(
            $customer,
            (int) $change->new_package_id
        );

        $isSamePackage =
            $activeSubForCompare &&
            (int) $activeSubForCompare->package_id === (int) $change->new_package_id &&
            (int) $activeSubForCompare->package_duration_id === (int) $change->new_package_duration_id;


        $newSub = $subscriptionResolver->resolve(
            $activeSubForCompare,
            $isSamePackage,
            $customer,
            $data,
            $periodStart,
            $periodEnd
        );

        /**
         * ======================================================
         * 4. Provisioning update (PPP / Mikrotik)
         * ======================================================
         * (pseudo-code, tergantung implementasi Anda)
         */
        app(ChangePackageService::class)
            ->applyPackageChange($customer, $activeSub, $newSub, $traceId);

        /**
         * ======================================================
         * 5. Mark change as applied
         * ======================================================
         */
        $change->update([
            'status' => 'applied',
        ]);

        LogHelper::logInfo(
            null,
            [
                'subscription_change_id' => $change->id,
                'customer_id'            => $customer->id,
                'old_subscription_id'    => $activeSub->id,
                'new_subscription_id'    => $newSub->id,
            ],
            200,
            'Scheduled subscription downgrade applied',
            $traceId,
            $timer->lap()
        );
    }
}
