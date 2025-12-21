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
use App\Services\Billings\BillingCreateService;
use App\Services\Billings\BillingUpdateCommentService;

class BillingService
{
    private BillingCreateService $billingCreateService;
    private BillingUpdateCommentService $billingUpdateCommentService;

    public function __construct(
        BillingCreateService $billingCreateService,
        BillingUpdateCommentService $billingUpdateCommentService
    ) {
        $this->billingCreateService = $billingCreateService;
        $this->billingUpdateCommentService = $billingUpdateCommentService;
    }

    public function createBillingPeriode(string $periode, string $traceId): ?BillingPeriode
    {
        $timer = new ExecutionTimer();
        $context = [
            'periode' => $periode,
            'action'  => 'create_billing_periode'
        ];

        try {
            LogHelper::logInfo(null, $context, 200, '[START] create Billing Periodically', $traceId, $timer->lap());

            $periodeDate = Carbon::createFromFormat('F Y', $periode);

            $billingPeriode = BillingPeriode::firstOrCreate(
                ['name' => $periode],
                [
                    'month' => $periodeDate->format('F'),
                    'year'  => $periodeDate->format('Y'),
                ]
            );

            LogHelper::logInfo(null, array_merge($context, [
                'billing_periode_id' => $billingPeriode->id
            ]), 200, 'Billing periode ready', $traceId, $timer->lap());

            return $billingPeriode;
        } catch (\Throwable $e) {
            LogHelper::logError(null, array_merge($context, [
                'exception'         => get_class($e),
                'error'        => [
                    'code' => $e->getCode(),
                    'message'     => $e->getMessage()
                ]
            ]), 500, 'Failed to create billing periode', $traceId, $timer->lap());

            return null;
        }
    }

    public function generateBillingsForPeriod(
        BillingPeriode $billingPeriode,
        string $traceId
    ): int {
        $timer = new ExecutionTimer();
        $totalBillingCreated = 0;

        $context = [
            'billing_periode_id'   => $billingPeriode->id,
            'billing_periode_name' => $billingPeriode->name,
            'action'               => 'generate_billings_for_period',
        ];

        LogHelper::logInfo(null, $context, 200, '[G1] Start billing generation', $traceId, 0);

        try {
            $routers   = Mikrotik::where('disabled', 'false')->get();
            $websystem = Websystem::where('slug', 'websystem')->first();

            if ($routers->isEmpty()) {
                LogHelper::logWarning(null, $context, 200, '[Step 1] No active routers found', $traceId, $timer->lap());
                return 0;
            }

            foreach ($routers as $router) {

                $routerContext = array_merge($context, [
                    'router_id' => $router->id,
                ]);

                $customers = Customer::where('disabled', 'false')
                    ->where('activation', 'true')
                    ->whereNotNull('activation_date')
                    ->where('mikrotik_id', $router->id)
                    ->get();

                foreach ($customers as $customer) {

                    DB::transaction(function () use (
                        $customer,
                        $router,
                        $routerContext,
                        $billingPeriode,
                        $websystem,
                        $traceId,
                        &$totalBillingCreated,
                    ) {
                        $timer = new ExecutionTimer();
                        $sub = CustomerSubscription::with(['customer', 'package'])
                            ->where('customer_id', $customer->id)
                            ->where('status', 'active')
                            ->first();

                        if (!$sub) {
                            LogHelper::logInfo(
                                null,
                                $routerContext,
                                200,
                                '[R2.1] No active subscription for customer',
                                $traceId,
                                $timer->lap()
                            );
                            return;
                        }

                        $subContext = array_merge($routerContext, [
                            'customer_id'              => $customer->id,
                            'customer_subscription_id' => $sub->id,
                        ]);

                        $oldBsp = BillingSubscriptionPeriode::where('customer_subscription_id', $sub->id)
                            ->where('status', 'active')
                            ->first();

                        if ($oldBsp && $oldBsp->billing_periode_id == $billingPeriode->id) {
                            LogHelper::logInfo(
                                null,
                                $subContext,
                                200,
                                '[S3.1] BSP already exists for current period',
                                $traceId,
                                $timer->lap()
                            );
                            return;
                        }

                        if ($oldBsp) {
                            $oldBsp->update(['status' => 'expired']);
                        }

                        $newBsp = BillingSubscriptionPeriode::create([
                            'customer_subscription_id' => $sub->id,
                            'billing_periode_id'       => $billingPeriode->id,
                            'status'                   => 'active',
                        ]);

                        $this->extendSubscriptionEndDate($sub, $billingPeriode);

                        if (Billing::where('billing_subscription_id', $newBsp->id)->exists()) {
                            LogHelper::logWarning(
                                null,
                                array_merge($subContext, ['bsp_id' => $newBsp->id]),
                                409,
                                '[B4.1] Billing already exists',
                                $traceId,
                                $timer->lap()
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

                        $totalBillingCreated++;

                        // Dispatch Mikrotik job AFTER commit
                        $autoIsolir = AutoIsolir::where('mikrotik_id', $router->id)->first();
                        if ($autoIsolir) {
                            $this->billingUpdateCommentService
                                ->dispatchBillingJobForCustomer(
                                    $customer,
                                    $router,
                                    $autoIsolir,
                                    $sub->price_final,
                                    $traceId
                                );
                        }
                    });
                }
            }

            LogHelper::logWarning(
                null,
                array_merge($context, ['totalBillingCreated' => $totalBillingCreated]),
                200,
                '[FINISH] Billing created successfully',
                $traceId,
                $timer->lap()
            );

            return $totalBillingCreated;
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
                'Failed generating billings',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }

    private function extendSubscriptionEndDate(
        CustomerSubscription $sub,
        BillingPeriode $billingPeriode
    ): void {
        // Ambil tahun & bulan dari periode (mis. "January 2026")
        $periodDate = Carbon::createFromFormat('F Y', $billingPeriode->name);

        // Set ke tanggal 15 bulan tsb
        $newEndDate = $periodDate->copy()->day(15)->endOfDay();

        // Update hanya jika lebih kecil (safety)
        if (!$sub->end_date || Carbon::parse($sub->end_date)->lt($newEndDate)) {
            $sub->update([
                'end_date' => $newEndDate,
            ]);
        }
    }
}
