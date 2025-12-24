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

    public function __construct(
        BillingCreateService $billingCreateService,
        BillingUpdateCommentService $billingUpdateCommentService
    ) {
        $this->billingCreateService = $billingCreateService;
        $this->billingUpdateCommentService = $billingUpdateCommentService;
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
                        $sub = CustomerSubscription::where('customer_id', $customer->id)
                            ->where('status', 'active')
                            ->lockForUpdate()
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
                            ->lockForUpdate()
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

                        $this->extendCustomerAddons($customer, $billingPeriode);

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
                            $priceFinal =  $sub->price_final;
                            DB::afterCommit(function () use ($customer, $router, $autoIsolir, $priceFinal, $traceId) {
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
        $newEndDate = $this->resolveBillingPeriodeEndDate($billingPeriode);

        if ($sub->end_date) {
            $currentEnd = Carbon::parse($sub->end_date)->endOfDay();

            if ($currentEnd->gte($newEndDate)) {
                return; // tidak perlu extend
            }
        }

        $sub->update([
            'end_date' => $newEndDate,
        ]);
    }

    private function resolveBillingPeriodeEndDate(
        BillingPeriode $billingPeriode
    ): Carbon {
        if ($billingPeriode->end_date) {
            return Carbon::parse($billingPeriode->end_date)->endOfDay();
        }

        try {
            return Carbon::createFromFormat('F Y', $billingPeriode->name)
                ->endOfMonth()
                ->endOfDay();
        } catch (\Throwable $e) {
            throw new DomainException(
                "Invalid billing period format: {$billingPeriode->name}",
                previous: $e
            );
        }
    }

    private function extendCustomerAddons(Customer $customer, BillingPeriode $billingPeriode): void
    {
        $custAddons = CustomerAddon::where('customer_id', $customer->id)
            ->where('is_active', true)->get();

        $newEnd = Carbon::parse($billingPeriode->end_date)->endOfDay();

        if ($custAddons) {

            foreach ($custAddons as $custAddon) {
                if (!$custAddon->end_date || Carbon::parse($custAddon->end_date)->endOfDay()->lt($newEnd)) {
                    $custAddon->update(['end_date' => $newEnd]);
                }
            }
        }
    }
}
