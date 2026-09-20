<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Billings\BillingPeriode;
use App\Models\Billings\BillingSubscriptionPeriode;
use App\Models\Billings\Billing;
use App\Models\Websystem;
use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;
use App\Services\Billings\BillingCreateService;
use App\Services\Billings\BillingExtensionService;
use Illuminate\Support\Facades\DB;

class HealMissingBilling extends Command
{
    protected $signature = 'billing:heal-missing {billing_periode_id}';
    protected $description = 'Heal missing billings caused by paused/terminated subscriptions';

    public function __construct(
        private BillingExtensionService $billingExtensionService,
        private BillingCreateService $billingCreateService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();
        $periodeId = (int) $this->argument('billing_periode_id');

        $periode = BillingPeriode::find($periodeId);
        if (!$periode) {
            $this->error("Billing periode not found.");
            return Command::FAILURE;
        }

        $this->info("▶ Healing billing for period: {$periode->name}");

        $stats = [
            'reactivated'          => 0,
            'created'              => 0,
            'skip_no_subscription' => 0,
            'skip_expired'         => 0,
            'skip_already_billed'  => 0,
        ];

        $websystem = Websystem::first();
        if (!$websystem) {
            $this->error('Websystem config not found.');
            return Command::FAILURE;
        }

        Customer::where('disabled', 'false')
            ->where('activation', 'true')
            ->chunkById(200, function ($customers) use (
                $periode,
                $websystem,
                $traceId,
                $timer,
                &$stats
            ) {
                foreach ($customers as $customer) {

                    DB::transaction(function () use (
                        $customer,
                        $periode,
                        $websystem,
                        $traceId,
                        $timer,
                        &$stats
                    ) {
                        // Lock customer
                        $customer = Customer::where('id', $customer->id)
                            ->lockForUpdate()
                            ->first();

                        $sub = CustomerSubscription::latestForCustomer($customer->id);

                        if (!$sub) {
                            $stats['skip_no_subscription']++;
                            return;
                        }

                        // 🔄 Reactivate
                        if ($sub->shouldBeReactivated($customer)) {
                            $sub->update([
                                'status'        => 'active',
                                'terminated_at' => null,
                            ]);

                            $stats['reactivated']++;

                            LogHelper::logInfo(
                                null,
                                [
                                    'customer_id'     => $customer->id,
                                    'subscription_id' => $sub->id,
                                    'reason'          => 'HEALING_SCRIPT',
                                ],
                                200,
                                'SUBSCRIPTION.REACTIVATED',
                                $traceId,
                                $timer->lap()
                            );
                        }

                        // 🔒 BSP active guard
                        $oldBsp = BillingSubscriptionPeriode::where('customer_subscription_id', $sub->id)
                            ->where('status', 'active')
                            ->orderByDesc('billing_periode_id')
                            ->lockForUpdate()
                            ->first();

                        if ($oldBsp?->billing_periode_id === $periode->id) {
                            $stats['skip_already_billed']++;
                            return;
                        }

                        if ($oldBsp) {
                            $oldBsp->update(['status' => 'expired']);

                            LogHelper::logInfo(
                                null,
                                [
                                    'customer_id' => $customer->id,
                                    'subscription_id' => $sub->id,
                                    'expired_bsp_id' => $oldBsp->id,
                                    'reason' => 'HEALING_SCRIPT',
                                ],
                                200,
                                'BILLING.BSP.EXPIRED',
                                $traceId,
                                $timer->lap()
                            );
                        }

                        if (Billing::whereHas('billingSubscription', function ($q) use ($sub, $periode) {
                            $q->where('customer_subscription_id', $sub->id)
                                ->where('billing_periode_id', $periode->id);
                        })->exists()) {
                            $stats['skip_already_billed']++;
                            return;
                        }

                        // Create BSP
                        $newBsp = BillingSubscriptionPeriode::create([
                            'customer_subscription_id' => $sub->id,
                            'billing_periode_id'       => $periode->id,
                            'status'                   => 'active',
                        ]);

                        // Create billing
                        $this->billingCreateService->createBilling(
                            $websystem,
                            $customer,
                            $newBsp,
                            $periode,
                            $sub,
                            $traceId
                        );

                        $stats['created']++;

                        $this->billingExtensionService
                            ->extendSubscription($sub, $periode);

                        $this->billingExtensionService
                            ->extendCustomerAddons($customer, $periode);

                        LogHelper::logInfo(
                            null,
                            [
                                'customer_id'     => $customer->id,
                                'subscription_id' => $sub->id,
                                'billing_periode' => $periode->name,
                                'reason'          => 'HEALING_SCRIPT',
                            ],
                            201,
                            'BILLING.HEAL.CREATED',
                            $traceId,
                            $timer->lap()
                        );
                    });
                }
            });

        // ===== FINISH =====
        $this->info("✔ Healing completed");
        $this->table(
            ['Metric', 'Value'],
            collect($stats)->map(fn($v, $k) => [$k, $v])->values()
        );

        LogHelper::logInfo(
            null,
            [
                'billing_periode_id' => $periode->id,
                'summary'            => $stats,
            ],
            200,
            'BILLING.HEAL.FINISH',
            $traceId,
            $timer->lap()
        );

        return Command::SUCCESS;
    }
}
