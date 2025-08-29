<?php

namespace App\Services\Billings;

use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\CustomerSubscription;
use App\Models\Websystem;
use App\Models\Billing\BillingSubscriptionPeriode;
use App\Models\SubscriptionDuration;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Jobs\CreateBillingJob;
use App\Jobs\CreateBillingIpStaticJob;
use App\Helpers\LogHelper;

class BillingService
{
    public function createBillingPeriode(string $periode, string $traceId): ?BillingPeriode
    {
        $startTime = microtime(true);
        $context = [
            'periode' => $periode,
            'action' => 'create_billing_periode'
        ];

        try {
            LogHelper::logInfo(null, $context, 200, 'Starting createBillingPeriode', $traceId);

            $periodeExplode = explode(' ', $periode);
            $billingPeriode = BillingPeriode::create([
                'name' => $periode,
                'month' => $periodeExplode[0] ?? null,
                'year' => $periodeExplode[1] ?? null,
            ]);

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo(null, array_merge($context, [
                'billing_periode_id' => $billingPeriode->id,
                'execution_seconds' => $executionTime
            ]), 200, 'Billing periode created successfully', $traceId);

            return $billingPeriode;
        } catch (\Throwable $e) {
            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logError(null, array_merge($context, [
                'execution_seconds' => $executionTime,
                'exception' => get_class($e),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]), 500, 'Failed to create billing periode', $traceId);

            return null;
        }
    }

    public function generateBillingsForPeriod(int $periodeId, string $traceId): int
    {
        $startTime = microtime(true);
        $context = [
            'billing_periode' => $periodeId,
            'action' => 'generate_billings_for_period'
        ];
        $totalBillingCreated = 0;

        try {
            LogHelper::logInfo(null, $context, 200, 'Starting billing generation', $traceId);

            $billingPeriode = BillingPeriode::find($periodeId);
            if (!$billingPeriode) {
                LogHelper::logWarning(null, $context, 200, 'No periode found', $traceId);
                return 0;
            }

            // dd($billingPeriode);
            $websystem = Websystem::where('slug', 'websystem')->first();
            $routers = Mikrotik::where('disabled', 'false')->get();

            if ($routers->isEmpty()) {
                LogHelper::logWarning(null, $context, 200, 'No active routers found', $traceId);
                return 0;
            }

            foreach ($routers as $router) {
                $customers = Customer::with('user', 'paket')
                    ->where('disabled', 'false')
                    ->where('activation', 'true')
                    ->where('mikrotik_id', $router->id)
                    ->get();

                if ($customers->isEmpty()) {
                    continue;
                }

                $customerPaketPairs = $customers->map(fn($c) => [
                    'customer_id' => $c->id,
                    'paket_id' => $c->paket_id
                ])->toArray();

                $customerSubscriptions = CustomerSubscription::where(function ($query) use ($customerPaketPairs) {
                    foreach ($customerPaketPairs as $pair) {
                        $query->orWhere(
                            fn($q) =>
                            $q->where('customer_id', $pair['customer_id'])
                                ->where('paket_id', $pair['paket_id'])
                        );
                    }
                })->get()->keyBy(fn($cs) => $cs->customer_id . '-' . $cs->paket_id);

                $existingBillingSubscriptionIds = BillingSubscriptionPeriode::where('billing_periode_id', $billingPeriode->id)
                    ->pluck('customer_subscription_id')
                    ->toArray();

                $customerSubscriptionsUnbilled = $customerSubscriptions->filter(
                    fn($cs) => !in_array($cs->id, $existingBillingSubscriptionIds)
                );

                if ($customerSubscriptionsUnbilled->isEmpty()) {
                    continue;
                }

                $paketIds = $customerSubscriptionsUnbilled->pluck('paket_id')->unique()->toArray();
                $subscriptionDurations = SubscriptionDuration::whereIn('paket_id', $paketIds)->get()->keyBy('paket_id');
                $customerIdsToBill = $customerSubscriptionsUnbilled->pluck('customer_id')->unique()->toArray();
                $customersToBill = $customers->whereIn('id', $customerIdsToBill);

                $createdCount = $this->createBillingsForCustomers(
                    $customersToBill,
                    $billingPeriode,
                    $websystem,
                    $router,
                    $traceId,
                    $customerSubscriptionsUnbilled,
                    $subscriptionDurations
                );

                if ($createdCount > 0) {
                    LogHelper::logInfo(null, array_merge($context, [
                        'router_id' => $router->id,
                        'created_billings' => $createdCount
                    ]), 200, "Billings created for {$router->name}", $traceId);
                }

                $totalBillingCreated += $createdCount;
            }

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo(null, array_merge($context, [
                'total_billing_created' => $totalBillingCreated,
                'execution_seconds' => $executionTime
            ]), 200, 'Billing generation finished', $traceId);

            return $totalBillingCreated;
        } catch (\Throwable $e) {
            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logError(null, array_merge($context, [
                'execution_seconds' => $executionTime,
                'exception' => get_class($e),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]), 500, 'Billing generation failed', $traceId);

            return 0;
        }
    }

    private function createBillingsForCustomers(
        $customers,
        BillingPeriode $billingPeriode,
        $websystem,
        $router,
        string $traceId,
        $customerSubscriptions,
        $subscriptionDurations
    ): int {
        $startTime = microtime(true);
        $totalBillingCreated = 0;

        try {
            LogHelper::logInfo(null, [
                'router_id' => $router->id,
                'total_customers' => count($customers),
                'billing_periode' => $billingPeriode->name
            ], 200, "Start creating billings for customers.", $traceId);

            $autoIsolir = AutoIsolir::where('mikrotik_id', $router->id)->first();

            // Preload data
            $billingSubscriptionPeriodesForPeriode = BillingSubscriptionPeriode::whereIn(
                'customer_subscription_id',
                $customerSubscriptions->pluck('id')->toArray()
            )->where('billing_periode_id', $billingPeriode->id)
                ->get()
                ->keyBy('customer_subscription_id');

            $existingBillingSubscriptionPeriodeIds = Billing::whereIn(
                'billing_subscription_id',
                $billingSubscriptionPeriodesForPeriode->pluck('id')->toArray()
            )->pluck('billing_subscription_id')->toArray();

            foreach ($customers as $customer) {
                $key = $customer->id . '-' . $customer->paket_id;

                if (!isset($customerSubscriptions[$key])) {
                    LogHelper::logWarning(null, [
                        'customer_id' => $customer->id
                    ], 404, "Customer subscription not found, skipping.", $traceId);
                    continue;
                }

                $customerSubscription = $customerSubscriptions[$key];
                $billingSubscriptionPeriode = $billingSubscriptionPeriodesForPeriode->get($customerSubscription->id);

                if ($billingSubscriptionPeriode && in_array($billingSubscriptionPeriode->id, $existingBillingSubscriptionPeriodeIds)) {
                    continue; // sudah ada billing
                }

                // Hitung harga, diskon, dan pajak
                $customerDiscount = $this->calculateCustomerDiscount($customer, $billingPeriode->name);
                $subDuration = $subscriptionDurations[$customer->paket_id] ?? null;

                $discountPaket = $this->calculatePaketDiscount($subDuration, $customer->paket->price);
                $discountAmount = $customerDiscount + $discountPaket;
                $priceAfterDiscount = max(0, $customer->paket->price - $discountAmount);

                $customerTax = ($websystem->enable_tax == 'true')
                    ? ceil($priceAfterDiscount * ($websystem->tax_rate / 100))
                    : 0;

                $totalAmount = $priceAfterDiscount + $customerTax;

                // Buat BillingSubscriptionPeriode
                $billingSubscriptionPeriode = BillingSubscriptionPeriode::create([
                    'customer_subscription_id' => $customerSubscription->id,
                    'billing_periode_id' => $billingPeriode->id,
                    'billing_periode' => $billingPeriode->name,
                    'is_paid' => false,
                ]);

                // Buat Billing
                Billing::create([
                    'slug' => str($customer->id . '-' . Str::random(4))->slug(),
                    'billing_number' => str($customer->id . '-' . Str::random(4) . '-' .
                        Carbon::parse($billingPeriode->name)->format('m-Y'))->slug(),
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_address' => $customer->address,
                    'billing_subscription_id' => $billingSubscriptionPeriode->id,
                    'base_price' => $customer->paket->price,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $priceAfterDiscount,
                    'total_amount' => $totalAmount,
                    'tax_amount' => $customerTax,
                    'created_name' => auth()->user()->name ?? optional($customer->user)->name,
                    'status' => 'BL',
                    'due_date' => Carbon::parse('15 ' . $billingPeriode->name)->endOfDay(),
                ]);

                LogHelper::logInfo(null, [
                    'customer_id' => $customer->id,
                    'billing_subscription_periode_id' => $billingSubscriptionPeriode->id,
                    'amount' => $totalAmount
                ], 201, "Billing created for {$customer->name}.", $traceId);

                $this->dispatchBillingJobForCustomer($customer, $router, $autoIsolir, $totalAmount, $traceId);

                $totalBillingCreated++;
            }

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo(null, [
                'router_id' => $router->id,
                'total_created' => $totalBillingCreated,
                'execution_time_sec' => $executionTime
            ], 200, "Finished creating billings for customers.", $traceId);

            return $totalBillingCreated;
        } catch (\Exception $e) {
            $executionTime = round(microtime(true) - $startTime, 3);

            LogHelper::logError(null, [
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'execution_time_sec' => $executionTime
            ], 500, "Error occurred while creating billings.", $traceId);

            throw $e;
        }
    }

    public function calculateCustomerDiscount(Customer $customer, string $periodeName): float
    {
        $discount = optional($customer->member)->discount ?? 0;

        return ($customer->billing_option === 1 ||
            ($customer->billing_option === 2 && $customer->billing_period === $periodeName))
            ? $discount
            : 0;
    }

    public function calculatePaketDiscount($subDuration, float $basePrice): float
    {
        if (!$subDuration || $subDuration->discount_type == 'none') {
            return 0;
        }

        if ($subDuration->discount_type == 'percent') {
            return $basePrice * ($subDuration->discount_value / 100);
        }

        if ($subDuration->discount_type == 'amount') {
            return $subDuration->discount_value;
        }

        return 0;
    }

    private function dispatchBillingJobForCustomer(
        Customer $customer,
        $router,
        $autoIsolir,
        float $price,
        string $traceId
    ): void {
        $startTime = microtime(true);

        try {
            $comment = $price > 0
                ? $autoIsolir->comment_unpayment
                : $autoIsolir->comment_payment;

            $jobs = [
                'ppp' => [
                    'class' => CreateBillingJob::class,
                    'params' => [$router, $comment, $customer->secret_id, $traceId],
                    'description' => 'PPP service',
                ],
                'ip_static' => [
                    'class' => CreateBillingIpStaticJob::class,
                    'params' => [$router, $customer->simpleque_id, $comment, $traceId],
                    'description' => 'IP Static service',
                ],
            ];

            $serviceType = $customer->internet_service;

            if (isset($jobs[$serviceType])) {
                $jobConfig = $jobs[$serviceType];
                dispatch(new $jobConfig['class'](...$jobConfig['params']))
                    ->onQueue('default');

                LogHelper::logInfo(
                    null,
                    [
                        'customer_id' => $customer->id,
                        'router_id' => $router->id,
                        'internet_service' => $serviceType,
                        'comment' => $comment,
                        'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    ],
                    200,
                    "Dispatched {$jobConfig['class']} for {$jobConfig['description']}.",
                    $traceId
                );
            } else {
                LogHelper::logWarning(
                    null,
                    [
                        'customer_id' => $customer->id,
                        'internet_service' => $serviceType,
                        'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    ],
                    400,
                    "Unknown internet service type. No job dispatched.",
                    $traceId
                );
            }
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'customer_id' => $customer->id,
                    'router_id' => $router->id,
                    'internet_service' => $customer->internet_service,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ],
                500,
                "Failed to dispatch billing job.",
                $traceId
            );
            throw $e;
        }
    }
}
