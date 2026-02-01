<?php

namespace App\Services\Billings;

use App\Helpers\ExecutionTimer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Mikrotik;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Billings\BillingSubscriptionPeriode;
use App\Models\Websystem;
use App\Models\AutoIsolir;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerAddon;
use App\Services\Billings\BillingCreateService;
use App\Services\Billings\BillingUpdateCommentService;
use DomainException;

class BillingService
{
    private BillingCreateService $billingCreateService;
    private BillingUpdateCommentService $billingUpdateCommentService;
    private BillingExtensionService $billingExtensionService;

    public function __construct(
        BillingCreateService $billingCreateService,
        BillingUpdateCommentService $billingUpdateCommentService,
        BillingExtensionService $billingExtensionService
    ) {
        $this->billingCreateService = $billingCreateService;
        $this->billingUpdateCommentService = $billingUpdateCommentService;
        $this->billingExtensionService = $billingExtensionService;
    }

    public function createBillingPeriode(string $periode): BillingPeriode
    {
        return DB::transaction(function () use ($periode) {

            $periodeDate = Carbon::createFromFormat('F Y', $periode);

            // 1. Close hanya periode yang aktif
            BillingPeriode::where('status', 'active')
                ->update(['status' => 'closed']);

            // 2. Ambil atau buat periode
            $billingPeriode = BillingPeriode::firstOrNew(
                ['name' => $periode]
            );

            // 3. Pastikan data konsisten
            $billingPeriode->fill([
                'month'      => $periodeDate->format('F'),
                'year'       => $periodeDate->format('Y'),
                'start_date' => $periodeDate->copy()->startOfMonth()->startOfDay(),
                'end_date'   => $periodeDate->copy()->endOfMonth()->endOfDay(),
                'status'     => 'active',
            ]);

            $billingPeriode->save();

            return $billingPeriode;
        });
    }

    public function generateBillingsForPeriod(
        BillingPeriode $billingPeriode,
        string $traceId
    ): int {
        $timer = new ExecutionTimer();

        $context = [
            'billing_periode_id'   => $billingPeriode->id,
            'billing_periode_name' => $billingPeriode->name,
            'action'               => 'generate_billings_for_period',
        ];

        LogHelper::logInfo(null, $context, 200, 'BILLING.START', $traceId, 0);

        try {
            $routers   = Mikrotik::where('disabled', 'false')->get();
            $websystem = Websystem::where('slug', 'websystem')->first();

            if ($routers->isEmpty()) {
                LogHelper::logInfo(
                    null,
                    $context,
                    200,
                    'BILLING.NO_ACTIVE_ROUTERS',
                    $traceId,
                    $timer->lap()
                );
                return 0;
            }

            $stats = [
                'created'              => 0,
                'skip_no_subscription' => 0,
                'skip_not_eligible'    => 0,
            ];

            foreach ($routers as $router) {

                $customers = Customer::where('mikrotik_id', $router->id)
                    ->where('disabled', 'false')
                    ->where('activation', 'true')
                    ->get();

                foreach ($customers as $customer) {

                    DB::transaction(function () use (
                        $customer,
                        $router,
                        $billingPeriode,
                        $websystem,
                        $traceId,
                        &$stats
                    ) {
                        $txTimer = new ExecutionTimer();

                        // Lock customer
                        $customer = Customer::where('id', $customer->id)
                            ->lockForUpdate()
                            ->first();

                        // Latest subscription
                        $sub = CustomerSubscription::where('customer_id', $customer->id)
                            ->orderByDesc('created_at')
                            ->lockForUpdate()
                            ->first();

                        if (!$sub) {
                            $stats['skip_no_subscription']++;
                            return;
                        }

                        // 🔄 Auto-reactivate subscription jika customer masih aktif
                        if ($sub->shouldBeReactivated($customer)) {

                            $oldStatus = $sub->status;

                            $sub->update([
                                'status'        => 'active',
                                'terminated_at' => null,
                            ]);

                            LogHelper::logInfo(
                                null,
                                [
                                    'customer_id'        => $customer->id,
                                    'subscription_id'    => $sub->id,
                                    'old_status'         => $oldStatus,
                                    'new_status'         => 'active',
                                    'reason'             => 'CUSTOMER_STILL_ACTIVE',
                                ],
                                200,
                                'SUBSCRIPTION.REACTIVATED',
                                $traceId,
                                $txTimer->lap()
                            );
                        }

                        if (!$sub->eligibleForBilling($customer)) {
                            $stats['skip_not_eligible']++;

                            LogHelper::logInfo(
                                null,
                                [
                                    'router_id'       => $router->id,
                                    'customer_id'     => $customer->id,
                                    'subscription_id' => $sub->id,
                                    'reason'          => 'NOT_ELIGIBLE',
                                ],
                                200,
                                'BILLING.SKIP.CUSTOMER_NOT_ACTIVE',
                                $traceId,
                                $txTimer->lap()
                            );
                            return;
                        }

                        // Active BSP (idempotency guard)
                        $oldBsp = BillingSubscriptionPeriode::where('customer_subscription_id', $sub->id)
                            ->where('status', 'active')
                            ->orderByDesc('billing_periode_id')
                            ->lockForUpdate()
                            ->first();

                        if ($oldBsp?->billing_periode_id === $billingPeriode->id) {
                            LogHelper::logInfo(
                                null,
                                [
                                    'router_id'          => $router->id,
                                    'customer_id'        => $customer->id,
                                    'subscription_id'    => $sub->id,
                                    'billing_periode_id' => $billingPeriode->id,
                                ],
                                200,
                                'BILLING.SKIP.BSP_ALREADY_EXISTS',
                                $traceId,
                                $txTimer->lap()
                            );
                            return;
                        }

                        if ($oldBsp) {
                            $oldBsp->update(['status' => 'expired']);

                            LogHelper::logInfo(
                                null,
                                [
                                    'customer_subscription_id' => $sub->id,
                                    'expired_bsp_id'           => $oldBsp->id,
                                ],
                                200,
                                'BILLING.BSP.EXPIRED',
                                $traceId,
                                $txTimer->lap()
                            );
                        }

                        $newBsp = BillingSubscriptionPeriode::create([
                            'customer_subscription_id' => $sub->id,
                            'billing_periode_id'       => $billingPeriode->id,
                            'status'                   => 'active',
                        ]);

                        if (Billing::where('billing_subscription_id', $newBsp->id)->exists()) {
                            LogHelper::logWarning(
                                null,
                                [
                                    'customer_id' => $customer->id,
                                    'bsp_id'      => $newBsp->id,
                                ],
                                409,
                                'BILLING.SKIP.BILLING_ALREADY_EXISTS',
                                $traceId,
                                $txTimer->lap()
                            );
                            return;
                        }

                        $this->billingCreateService->createBilling(
                            $websystem,
                            $customer,
                            $newBsp,
                            $billingPeriode,
                            $sub,
                            $traceId
                        );

                        LogHelper::logInfo(
                            null,
                            [
                                'router_id'          => $router->id,
                                'customer_id'        => $customer->id,
                                'subscription_id'    => $sub->id,
                                'billing_periode_id' => $billingPeriode->id,
                            ],
                            201,
                            'BILLING.CREATE.SUCCESS',
                            $traceId,
                            $txTimer->lap()
                        );

                        $stats['created']++;

                        // Extend only after billing success
                        $this->billingExtensionService
                            ->extendSubscription($sub, $billingPeriode);

                        $this->billingExtensionService
                            ->extendCustomerAddons($customer, $billingPeriode);

                        // Dispatch AFTER COMMIT
                        $autoIsolir = AutoIsolir::where('mikrotik_id', $router->id)->first();

                        if ($autoIsolir) {
                            $priceFinal = $sub->price_final;

                            DB::afterCommit(function () use (
                                $customer,
                                $router,
                                $autoIsolir,
                                $priceFinal,
                                $traceId
                            ) {
                                $this->billingUpdateCommentService
                                    ->dispatchBillingJobForCustomer(
                                        $customer,
                                        $router,
                                        $autoIsolir,
                                        $priceFinal,
                                        $traceId
                                    );
                            });
                        }
                    });
                }
            }

            LogHelper::logInfo(
                null,
                [
                    'billing_periode_id' => $billingPeriode->id,
                    'summary' => $stats,
                ],
                200,
                'BILLING.FINISH',
                $traceId,
                $timer->lap()
            );

            // 🔑 RETURN SELARAS DENGAN SUMMARY
            return $stats['created'];
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                array_merge($context, [
                    'exception' => get_class($e),
                    'error' => [
                        'code'    => $e->getCode(),
                        'message' => $e->getMessage(),
                    ],
                ]),
                500,
                'BILLING.FAILED',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }
}
