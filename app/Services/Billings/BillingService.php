<?php

namespace App\Services\Billings;

use App\Helpers\LogHelper;
use App\Helpers\SubscriptionPricingCalculator;
use App\Models\Customers\CustomerSubscription;
use App\Models\Mikrotik;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Billings\BillingSubscriptionPeriode;
use App\Models\Websystem;
use App\Models\AutoIsolir;
use Illuminate\Support\Facades\DB;
use App\Models\Customers\Customer;
use Carbon\Carbon;
use Throwable;

class BillingService
{
    public function __construct(
        private readonly BillingCreateService $billingCreateService,
        private readonly BillingUpdateCommentService $billingUpdateCommentService,
        private readonly BillingExtensionService $billingExtensionService
    ) {}

    public function createBillingPeriode(?string $periode = null, ?string $traceId = null): BillingPeriode
    {
        $periodeName = $periode ?: now()->format('F Y');
        $actionName  = 'Create Billing Periode';

        LogHelper::logInfo($actionName, ['periode' => $periodeName], 200, 'Memulai proses pembuatan periode billing', $traceId);

        try {
            return DB::transaction(function () use ($periodeName, $traceId, $actionName) {
                $periodeDate = Carbon::createFromFormat('F Y', $periodeName);

                $affectedRows = BillingPeriode::where('status', 'active')
                    ->update(['status' => 'closed']);

                LogHelper::logInfo($actionName, ['closed_periodes_count' => $affectedRows], 200, 'Menutup periode billing aktif sebelumnya', $traceId);

                $billingPeriode = BillingPeriode::firstOrNew(['name' => $periodeName]);

                $billingPeriode->fill([
                    'month'      => $periodeDate->format('F'),
                    'year'       => $periodeDate->format('Y'),
                    'start_date' => $periodeDate->copy()->startOfMonth()->startOfDay(),
                    'end_date'   => $periodeDate->copy()->endOfMonth()->endOfDay(),
                    'status'     => 'active',
                ]);

                $billingPeriode->save();

                LogHelper::logInfo($actionName, $billingPeriode->toArray(), 201, 'Berhasil membuat/memperbarui periode billing aktif', $traceId);

                return $billingPeriode;
            });
        } catch (Throwable $e) {
            LogHelper::logError($e, ['periode' => $periodeName], 500, 'Gagal membuat periode billing', $traceId);
            throw $e;
        }
    }

    public function generateBillingsForPeriod(BillingPeriode $billingPeriode, string $traceId): int
    {
        $context = [
            'billing_periode_id'   => $billingPeriode->id,
            'billing_periode_name' => $billingPeriode->name,
            'action'               => 'generate_billings_for_period',
        ];

        LogHelper::logInfo(null, $context, 200, 'BILLING.START', $traceId);

        try {
            $routers   = Mikrotik::where('disabled', 'false')->get();
            $websystem = Websystem::where('slug', 'websystem')->first();

            if ($routers->isEmpty()) {
                LogHelper::logInfo(null, $context, 200, 'BILLING.NO_ACTIVE_ROUTERS', $traceId);
                return 0;
            }

            $stats = [
                'created'              => 0,
                'skip_no_subscription' => 0,
                'skip_not_eligible'    => 0,
            ];

            foreach ($routers as $router) {
                $customers = Customer::where('mikrotik_id', $router->id)
                    ->where('is_active', true)
                    ->where('activation', true)
                    ->get();

                foreach ($customers as $customer) {
                    DB::transaction(function () use ($customer, $router, $billingPeriode, $websystem, $traceId, &$stats) {

                        $customer = Customer::where('id', $customer->id)->lockForUpdate()->first();

                        /** @var CustomerSubscription|null $sub */
                        $sub = CustomerSubscription::where('customer_id', $customer->id)
                            ->orderByDesc('created_at')
                            ->lockForUpdate()
                            ->first();

                        if (!$sub) {
                            $stats['skip_no_subscription']++;
                            return;
                        }

                        if ($sub->shouldBeReactivated($customer)) {
                            $oldStatus = $sub->status;
                            $sub->update([
                                'status'        => 'active',
                                'terminated_at' => null,
                            ]);

                            LogHelper::logInfo(
                                null,
                                [
                                    'customer_id'     => $customer->id,
                                    'subscription_id' => $sub->id,
                                    'old_status'      => $oldStatus,
                                    'new_status'      => 'active',
                                ],
                                200,
                                'SUBSCRIPTION.REACTIVATED',
                                $traceId
                            );
                        }

                        if (!$sub->eligibleForBilling($customer)) {
                            $stats['skip_not_eligible']++;
                            return;
                        }

                        // 💡 1. Normalisasi prorate menggunakan helper terpusat
                        $this->normalizeProratedSubscription($sub, $traceId);

                        // Idempotency Check
                        $oldBsp = BillingSubscriptionPeriode::where('customer_subscription_id', $sub->id)
                            ->where('status', 'active')
                            ->orderByDesc('billing_periode_id')
                            ->lockForUpdate()
                            ->first();

                        if ($oldBsp?->billing_periode_id === $billingPeriode->id) {
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

                        if (Billing::where('billing_subscription_id', $newBsp->id)->exists()) {
                            return;
                        }

                        // Kirim $sub yang nilainya sudah pasti normal
                        $this->billingCreateService->createBilling(
                            $websystem,
                            $customer,
                            $newBsp,
                            $billingPeriode,
                            $sub,
                            $traceId
                        );

                        $stats['created']++;

                        // 💡 2. Perpanjang end_date paket berlangganan & addons untuk periode berjalan
                        $this->billingExtensionService->extendSubscription($sub, $billingPeriode);
                        $this->billingExtensionService->extendCustomerAddons($customer, $billingPeriode);

                        $autoIsolir = AutoIsolir::where('mikrotik_id', $router->id)->first();

                        if ($autoIsolir) {
                            $priceFinal = $sub->price_final;

                            DB::afterCommit(function () use ($customer, $router, $autoIsolir, $priceFinal, $traceId) {
                                $this->billingUpdateCommentService->dispatchBillingJobForCustomer(
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

            LogHelper::logInfo(null, ['billing_periode_id' => $billingPeriode->id, 'summary' => $stats], 200, 'BILLING.FINISH', $traceId);

            return $stats['created'];
        } catch (Throwable $e) {
            LogHelper::logError(null, array_merge($context, ['exception' => get_class($e), 'error' => $e->getMessage()]), 500, 'BILLING.FAILED', $traceId);
            throw $e;
        }
    }

    /**
     * 💡 Mereset harga prorate ke harga paket normal bulanan (setelah diskon durasi)
     */
    private function normalizeProratedSubscription(CustomerSubscription $sub, ?string $traceId = null): void
    {
        if (!$sub->is_prorated) {
            return;
        }

        $oldPrice = $sub->price_final;

        // Eager load packageDuration untuk kalkulasi ulang
        $sub->loadMissing(['packageDuration.package', 'packageDuration.duration']);

        if ($sub->packageDuration) {
            // Hitung harga normal tanpa prorate (isProrateInput = false)
            $pricing = SubscriptionPricingCalculator::calculateSubscriptionPrice(
                packageDuration: $sub->packageDuration,
                isProrateInput: false
            );

            $sub->update([
                'price_final'          => $pricing['subscription_price'],
                'discount_type_final'  => $pricing['duration_discount_type'],
                'discount_value_final' => $pricing['duration_discount_value'],
                'is_prorated'          => false,
            ]);

            $sub->refresh();

            LogHelper::logInfo(
                null,
                [
                    'subscription_id' => $sub->id,
                    'customer_id'     => $sub->customer_id,
                    'old_price'       => $oldPrice,
                    'new_normal_price' => $pricing['subscription_price'],
                    'is_prorated'     => false,
                ],
                200,
                'SUBSCRIPTION.PRORATE_RESET_TO_NORMAL',
                $traceId
            );
        }
    }
}
