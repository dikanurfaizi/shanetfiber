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
use App\Models\Packages\Package;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
            'action'  => 'create_billing_periode'
        ];

        try {
            LogHelper::logInfo(null, $context, 200, 'Starting createBillingPeriode', $traceId);

            $periodeDate = Carbon::createFromFormat('F Y', $periode);

            $billingPeriode = BillingPeriode::firstOrCreate(
                ['name' => $periode],
                [
                    'month' => $periodeDate->format('F'),
                    'year'  => $periodeDate->format('Y'),
                ]
            );

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo(null, array_merge($context, [
                'billing_periode_id' => $billingPeriode->id,
                'execution_seconds'  => $executionTime
            ]), 200, 'Billing periode ready', $traceId);

            return $billingPeriode;
        } catch (\Throwable $e) {
            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logError(null, array_merge($context, [
                'execution_seconds' => $executionTime,
                'exception'         => get_class($e),
                'error_code'        => $e->getCode(),
                'error_message'     => $e->getMessage()
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

            /** @var BillingPeriode|null $billingPeriode */
            $billingPeriode = BillingPeriode::find($periodeId);
            if (!$billingPeriode) {
                LogHelper::logWarning(null, $context, 200, 'No periode found', $traceId);
                return 0;
            }

            $websystem = Websystem::where('slug', 'websystem')->first();
            $routers   = Mikrotik::where('disabled', 'false')->get();

            if ($routers->isEmpty()) {
                LogHelper::logWarning(null, $context, 200, 'No active routers found', $traceId);
                return 0;
            }

            foreach ($routers as $router) {
                // 1) Ambil semua customer aktif per router
                /** @var Collection<int,Customer> $customers */
                $customers = Customer::with(['user', 'package'])
                    ->where('disabled', 'false')
                    ->where('activation', 'true')
                    ->where('mikrotik_id', $router->id)
                    ->get();

                if ($customers->isEmpty()) {
                    continue;
                }

                // 2) Preload durations per package (keyed by package_id)
                $subscriptionDurations = $this->getSubscriptionDurationsIndexed(
                    $customers->pluck('package_id')->filter()->unique()->toArray()
                );

                // 3) Build map subscription existing (customer_id-package_id => CustomerSubscription)
                $customerSubscriptions = $this->buildCustomerSubscriptionsMap($customers);

                // 4) Ambil daftar subscription_id yang SUDAH dibuatkan BSP pada periode ini
                $existingBspSubscriptionIds = BillingSubscriptionPeriode::where('billing_periode_id', $billingPeriode->id)
                    ->pluck('customer_subscription_id')
                    ->toArray();

                // 5) Kelompok A: customers TANPA subscription sama sekali
                $customersWithoutSub = $customers->filter(function (Customer $c) use ($customerSubscriptions) {
                    $key = $this->subKey($c->id, $c->package_id);
                    return !$customerSubscriptions->has($key);
                });

                // 6) Kelompok B: subscription ADA tapi BELUM dibill (unbilled)
                /** @var Collection<int,CustomerSubscription> $subsUnbilled */
                $subsUnbilled = $customerSubscriptions->filter(function (CustomerSubscription $cs) use ($existingBspSubscriptionIds) {
                    return !in_array($cs->id, $existingBspSubscriptionIds, true)
                        && $cs->status === 'active';
                });

                // 7) Kelompok C: subscription ADA, AKTIF, tapi end_date belum align ke periode → perlu extend dulu
                $subsToExtend = $subsUnbilled->filter(function (CustomerSubscription $cs) use ($billingPeriode) {
                    if (empty($cs->start_date)) return true; // koreksi data
                    $activationDate = Carbon::parse($cs->start_date);
                    $expectedEnd    = $this->computeEndDateForPeriod($activationDate, $billingPeriode);
                    if (!$expectedEnd) return true;
                    return !Carbon::parse($cs->end_date)->isSameDay($expectedEnd);
                });

                // === Proses A: create/extend untuk customers tanpa subscription, lalu buat BSP ===
                if ($customersWithoutSub->isNotEmpty()) {
                    $createdForNewSubs = $this->autoCreateOrExtendSubscriptionsForPeriod(
                        $customersWithoutSub,
                        $subscriptionDurations,
                        $billingPeriode,
                        $router,
                        $traceId,
                        $context
                    );
                    $totalBillingCreated += $createdForNewSubs;
                }

                // refresh peta subscription setelah Proses A
                if ($customersWithoutSub->isNotEmpty()) {
                    $customerSubscriptions = $this->buildCustomerSubscriptionsMap($customers);
                }

                // === Proses C: extend end_date/price subscription aktif yang belum align periode ===
                if ($subsToExtend->isNotEmpty()) {
                    $extended = $this->extendExistingSubscriptionsForPeriod(
                        $subsToExtend,
                        $subscriptionDurations,
                        $billingPeriode,
                        $router,
                        $traceId,
                        $context
                    );
                    // refresh setelah extend agar data fresh
                    if ($extended > 0) {
                        $customerSubscriptions = $this->buildCustomerSubscriptionsMap($customers);
                    }
                }

                // === Proses B: create billing untuk subscription aktif yang belum dibill ===
                // (gunakan peta terbaru; susun ulang subsUnbilled dari peta setelah A/C)
                $existingBspSubscriptionIds = BillingSubscriptionPeriode::where('billing_periode_id', $billingPeriode->id)
                    ->pluck('customer_subscription_id')
                    ->toArray();

                $subsUnbilled = $customerSubscriptions->filter(function (CustomerSubscription $cs) use ($existingBspSubscriptionIds) {
                    return !in_array($cs->id, $existingBspSubscriptionIds, true)
                        && $cs->status === 'active';
                });

                if ($subsUnbilled->isNotEmpty()) {
                    $customerIdsToBill = $subsUnbilled->pluck('customer_id')->unique()->values();
                    $customersToBill   = $customers->whereIn('id', $customerIdsToBill);

                    $createdCount = $this->createBillingsForCustomers(
                        $customersToBill,
                        $billingPeriode,
                        $websystem,
                        $router,
                        $traceId,
                        $subsUnbilled,
                        $subscriptionDurations
                    );

                    if ($createdCount > 0) {
                        LogHelper::logInfo(
                            null,
                            array_merge($context, [
                                'router_id'        => $router->id,
                                'created_billings' => $createdCount
                            ]),
                            200,
                            "Billings created for {$router->name}",
                            $traceId
                        );
                    }

                    $totalBillingCreated += $createdCount;
                }
            }

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo(
                null,
                array_merge($context, [
                    'total_billing_created' => $totalBillingCreated,
                    'execution_seconds'     => $executionTime
                ]),
                200,
                'Billing generation finished',
                $traceId
            );

            return $totalBillingCreated;
        } catch (\Throwable $e) {
            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logError(
                null,
                array_merge($context, [
                    'execution_seconds' => $executionTime,
                    'exception'         => get_class($e),
                    'error_code'        => $e->getCode(),
                    'error_message'     => $e->getMessage()
                ]),
                500,
                'Billing generation failed',
                $traceId
            );

            return 0;
        }
    }

    // ====================== Helpers ======================

    private function buildCustomerSubscriptionsMap(Collection $customers): Collection
    {
        if ($customers->isEmpty()) return collect();

        $pairs = $customers->map(fn(Customer $c) => [
            'customer_id' => $c->id,
            'package_id'  => $c->package_id,
        ])->filter(fn($p) => $p['package_id'])->values()->all();

        if (empty($pairs)) return collect();

        $subsQuery = CustomerSubscription::query();
        $subsQuery->where(function ($q) use ($pairs) {
            foreach ($pairs as $pair) {
                $q->orWhere(function ($q2) use ($pair) {
                    $q2->where('customer_id', $pair['customer_id'])
                        ->where('package_id',  $pair['package_id']);
                });
            }
        });

        /** @var Collection<int,CustomerSubscription> $subs */
        $subs = $subsQuery->get();

        return $subs->keyBy(fn(CustomerSubscription $cs) => $this->subKey($cs->customer_id, $cs->package_id));
    }

    private function subKey(int $customerId, ?int $packageId): string
    {
        return $customerId . '-' . ($packageId ?? 0);
    }

    private function getSubscriptionDurationsIndexed(array $packageIds): Collection
    {
        if (empty($packageIds)) return collect();

        /** @var Collection<int,SubscriptionDuration> $rows */
        $rows = SubscriptionDuration::whereIn('package_id', $packageIds)->get();
        return $rows->keyBy('package_id');
    }

    private function computeEndDateForPeriod(?Carbon $activationDate, BillingPeriode $periode): ?Carbon
    {
        if (!$activationDate) return null;

        // Asumsi BillingPeriode punya month_start (YYYY-MM-01 00:00:00)
        $monthStart   = Carbon::parse($periode->month_start)->startOfDay();
        $daysInMonth  = $monthStart->daysInMonth;
        $targetDay    = min((int) $activationDate->day, $daysInMonth);

        return $monthStart->copy()->day($targetDay)->endOfDay();
    }

    /**
     * Price perlu update jika:
     * - discount berubah, ATAU
     * - (opsional) harga paket berubah (jika kamu menyimpan snapshot terakhir).
     */
    private function priceNeedsUpdate(CustomerSubscription $sub, Package $pkg, ?SubscriptionDuration $sd): bool
    {
        $discountChanged = false;
        if ($sd) {
            $discountChanged = ($sub->discount_type_final !== $sd->discount_type)
                || ((string) $sub->discount_value_final !== (string) $sd->discount_value);
        }

        // Jika kamu punya kolom snapshot harga paket sebelumnya (mis. package_price_snapshot),
        // aktifkan check ini:
        $packagePriceChanged = false;
        if (isset($sub->package_price_snapshot)) {
            $packagePriceChanged = ((float)$sub->package_price_snapshot !== (float)$pkg->price);
        }

        return $discountChanged || $packagePriceChanged || is_null($sub->price_final);
    }

    private function computeFinalPrice(Package $pkg, ?SubscriptionDuration $sd): float
    {
        $price = (float) $pkg->price;

        if (!$sd || !$sd->discount_type || !$sd->discount_value) {
            return max(0, $price);
        }

        if ($sd->discount_type === 'percent') {
            $value = (float) $sd->discount_value;
            $value = ($value > 1) ? $value / 100.0 : $value; // normalisasi 10 => 10%
            return max(0, $price - ($price * $value));
        }

        if ($sd->discount_type === 'amount') {
            return max(0, $price - (float) $sd->discount_value);
        }

        return max(0, $price);
    }

    // ====================== Proses A ======================

    private function autoCreateOrExtendSubscriptionsForPeriod(
        Collection $customersWithoutSub,
        Collection $subscriptionDurations,
        BillingPeriode $billingPeriode,
        Mikrotik $router,
        string $traceId,
        array $context
    ): int {
        $created = 0;

        if ($customersWithoutSub->isEmpty()) return 0;

        DB::transaction(function () use (
            $customersWithoutSub,
            $subscriptionDurations,
            $billingPeriode,
            $router,
            $traceId,
            $context,
            &$created
        ) {
            foreach ($customersWithoutSub as $cust) {
                if ($cust->disabled !== 'false' || $cust->activation !== 'true') {
                    continue;
                }

                if (empty($cust->activation_date)) {
                    LogHelper::logWarning(
                        null,
                        array_merge($context, [
                            'router_id'   => $router->id,
                            'customer_id' => $cust->id
                        ]),
                        200,
                        'Skip: customer has no activation_date',
                        $traceId
                    );
                    continue;
                }

                $activationDate = Carbon::parse($cust->activation_date)->startOfDay();
                $endDate        = $this->computeEndDateForPeriod($activationDate, $billingPeriode);
                if (!$endDate) {
                    LogHelper::logWarning(
                        null,
                        array_merge($context, [
                            'router_id'   => $router->id,
                            'customer_id' => $cust->id
                        ]),
                        200,
                        'Skip: cannot compute end_date for period',
                        $traceId
                    );
                    continue;
                }

                /** @var CustomerSubscription $subscription */
                $subscription = CustomerSubscription::firstOrNew([
                    'customer_id' => $cust->id,
                    'package_id'  => $cust->package_id,
                ]);

                // Koreksi start_date satu kali
                if (
                    !$subscription->exists || !$subscription->start_date
                    || !Carbon::parse($subscription->start_date)->isSameDay($activationDate)
                ) {
                    $subscription->start_date = $activationDate;
                }

                // End date ke periode berjalan (yyyy-mm-[activation-day] 23:59:59)
                $subscription->end_date = $endDate;
                $subscription->status   = 'active';

                // Pricing
                $sdForPkg = $subscriptionDurations->get($cust->package_id);
                if ($this->priceNeedsUpdate($subscription, $cust->package, $sdForPkg)) {
                    $subscription->discount_type_final    = $sdForPkg?->discount_type;
                    $subscription->discount_value_final   = $sdForPkg?->discount_value;
                    $subscription->price_final            = $this->computeFinalPrice($cust->package, $sdForPkg);
                    $subscription->subscription_duration_id = $sdForPkg?->id;
                    // optional snapshot harga agar compliant dengan rule “ubah bila harga paket berubah”
                    if (property_exists($subscription, 'package_price_snapshot')) {
                        $subscription->package_price_snapshot = $cust->package->price;
                    }
                }

                $subscription->save();

                // BSP untuk periode berjalan (unik per subscription_id + periode_id)
                BillingSubscriptionPeriode::firstOrCreate([
                    'customer_subscription_id' => $subscription->id,
                    'billing_periode_id'       => $billingPeriode->id,
                ], [
                    'billing_periode' => $billingPeriode->name,
                    'is_paid'         => false,
                    'is_expired'      => false,
                    'status'          => 'unpaid',
                ]);

                $created++;
            }

            if ($created > 0) {
                LogHelper::logInfo(
                    null,
                    array_merge($context, [
                        'router_id'         => $router->id,
                        'sub_auto_extended' => $created
                    ]),
                    200,
                    "Auto create/extend subscriptions done",
                    $traceId
                );
            }
        });

        return $created;
    }

    // ====================== Proses C ======================

    private function extendExistingSubscriptionsForPeriod(
        Collection $subsToExtend,
        Collection $subscriptionDurations,
        BillingPeriode $billingPeriode,
        Mikrotik $router,
        string $traceId,
        array $context
    ): int {
        $extended = 0;

        if ($subsToExtend->isEmpty()) return 0;

        DB::transaction(function () use (
            $subsToExtend,
            $subscriptionDurations,
            $billingPeriode,
            $router,
            $traceId,
            $context,
            &$extended
        ) {
            /** @var CustomerSubscription $sub */
            foreach ($subsToExtend as $sub) {
                $activationDate = $sub->start_date ? Carbon::parse($sub->start_date) : null;
                if (!$activationDate) {
                    // fallback: pakai customer.activation_date kalau ada relasi; skip bila tidak ada
                    /** @var Customer|null $cust */
                    $cust = Customer::find($sub->customer_id);
                    if (!$cust || empty($cust->activation_date)) {
                        LogHelper::logWarning(
                            null,
                            array_merge($context, [
                                'router_id'   => $router->id,
                                'customer_id' => $sub->customer_id,
                                'sub_id'      => $sub->id,
                            ]),
                            200,
                            'Skip: cannot extend subscription (no activation_date)',
                            $traceId
                        );
                        continue;
                    }
                    $activationDate = Carbon::parse($cust->activation_date)->startOfDay();
                    $sub->start_date = $activationDate;
                }

                $endDate = $this->computeEndDateForPeriod($activationDate, $billingPeriode);
                if (!$endDate) {
                    LogHelper::logWarning(
                        null,
                        array_merge($context, [
                            'router_id'   => $router->id,
                            'customer_id' => $sub->customer_id,
                            'sub_id'      => $sub->id,
                        ]),
                        200,
                        'Skip: cannot compute end_date (extend)',
                        $traceId
                    );
                    continue;
                }

                $sub->end_date = $endDate;
                $sub->status   = 'active';

                // pricing check
                /** @var Package $pkg */
                $pkg = Package::find($sub->package_id);
                $sd  = $subscriptionDurations->get($sub->package_id);

                if ($pkg && $this->priceNeedsUpdate($sub, $pkg, $sd)) {
                    $sub->discount_type          = $sd?->discount_type;
                    $sub->discount_value         = $sd?->discount_value;
                    $sub->price_final            = $this->computeFinalPrice($pkg, $sd);
                    if (property_exists($sub, 'package_price_snapshot')) {
                        $sub->package_price_snapshot = $pkg->price;
                    }
                }

                $sub->save();
                $extended++;
            }

            if ($extended > 0) {
                LogHelper::logInfo(
                    null,
                    array_merge($context, [
                        'router_id'   => $router->id,
                        'updated_sub' => $extended
                    ]),
                    200,
                    "Extended active subscriptions",
                    $traceId
                );
            }
        });

        return $extended;
    }

    // ====================== Proses B ======================

    /**
     * Buat Billing berdasarkan subscription aktif yang belum punya BSP pada periode ini.
     * Catatan: BSP akan disiapkan bila belum ada (defensive), namun idealnya sudah dibuat di A/C.
     */
    private function createBillingsForCustomers(
        Collection $customersToBill,
        BillingPeriode $billingPeriode,
        ?Websystem $websystem,
        Mikrotik $router,
        string $traceId,
        Collection $customerSubscriptionsUnbilled,
        Collection $subscriptionDurations
    ): int {
        if ($customersToBill->isEmpty()) return 0;

        $created = 0;

        /** @var Collection<string,CustomerSubscription> $subsByKey */
        $subsByKey = $customerSubscriptionsUnbilled->keyBy(
            fn(CustomerSubscription $cs) => $this->subKey($cs->customer_id, $cs->package_id)
        );

        // Ambil tanggal untuk nomor billing dari month_start (bukan name)
        $periodeStamp = Carbon::parse($billingPeriode->month_start)->format('Ym');

        DB::transaction(function () use (
            $customersToBill,
            $billingPeriode,
            $router,
            $traceId,
            $subsByKey,
            $websystem,
            $subscriptionDurations,
            $periodeStamp,
            &$created
        ) {
            /** @var Customer $cust */
            foreach ($customersToBill as $cust) {

                $subKey = $this->subKey($cust->id, $cust->package_id);
                /** @var CustomerSubscription|null $sub */
                $sub = $subsByKey->get($subKey);

                // Eligibility
                if (!$sub || $sub->status !== 'active') {
                    continue;
                }

                // Guard package null
                $packagePrice = (float) optional($cust->package)->price ?? 0.0;

                // Base amount = price_final pada subscription
                $baseAmount = (float) (($sub->price_final ?? 0.0));

                // Diskon pelanggan (opsional, gunakan nullsafe)
                $customerDiscount = $this->calculateCustomerDiscount($cust, $billingPeriode->name);

                // Diskon paket dari subscription_durations (keyed by package_id)
                $sd = $subscriptionDurations->get($cust->package_id);
                $discountPackage = $this->calculatePackageDiscount($sd, $packagePrice);

                // Subtotal = baseAmount - (customer + package) discount
                $subtotal = max(0, $packagePrice - ($customerDiscount + $discountPackage));

                // Pajak
                $enableTax = (bool) optional($websystem)->enable_tax;
                $taxRate   = (float) (optional($websystem)->tax_rate ?? 0.0); // contoh: 0.11 untuk 11%
                // Kadang tim menyimpan 11 bukan 0.11. Jika di sistemmu disimpan 11, ubah baris ini:
                // $taxRate = ((float) (optional($websystem)->tax_rate ?? 0.0)) / 100.0;

                $taxAmount = $enableTax ? ($subtotal * $taxRate) : 0.0;

                // Total
                // Lakukan pembulatan ke rupiah utuh (IDR), jika kebijakanmu tanpa sen
                $subtotal = round($subtotal, 0);
                $taxAmount = round($taxAmount, 0);
                $totalAmount = $subtotal + $taxAmount;

                // Status pembayaran & BSP flags (kebijakan: total 0 dianggap lunas)
                $statusPayment = 'BL'; // BL = belum lunas (ikuti konvensimu)
                $isPaid = false;
                $paidAt = null;
                $billSubStatus = 'unpaid';

                if ($totalAmount <= 0) {
                    $statusPayment = 'LS'; // lunas
                    $isPaid = true;
                    $paidAt = Carbon::now();
                    $billSubStatus = 'paid';
                }

                // Defensive: pastikan BSP ada (unik per sub_id + periode_id)
                $bsp = BillingSubscriptionPeriode::firstOrCreate([
                    'customer_subscription_id' => $sub->id,
                    'billing_periode_id'       => $billingPeriode->id,
                ], [
                    'billing_periode' => $billingPeriode->name,
                    'is_paid'         => $isPaid,
                    'is_expired'      => false,
                    'status'          => $billSubStatus,
                    'paid_at'         => $paidAt
                ]);

                // Due date fallback
                $dueDate = $sub->end_date
                    ? Carbon::parse($sub->end_date)
                    : Carbon::parse($billingPeriode->month_end)->endOfDay();

                // Nomor billing/invoice (deterministic + unik)
                $billingNumber = sprintf(
                    'INV-%s-%d-%d',
                    $periodeStamp,       // contoh: 202511
                    $sub->id,            // subscription id
                    $cust->id
                );

                // Slug unik (jangan andalkan slug untuk uniqueness bisnis)
                $slug = Str::slug($cust->id . '-' . Str::random(6));

                // Create or ignore kalau sudah ada (unique key di DB harus ada!)
                $billing = Billing::firstOrCreate([
                    'billing_subscription_id' => $bsp->id,
                ], [
                    'slug'              => $slug,
                    'billing_number'    => $billingNumber,
                    'customer_id'       => $cust->id,
                    'customer_name'     => $cust->name,
                    'customer_address'  => $cust->address,
                    'base_price'        => $packagePrice,           // ← dari subscription.price_final
                    'discount_amount'   => ($customerDiscount + $discountPackage),
                    'subtotal'          => $subtotal,             // ← setelah diskon
                    'tax_amount'        => $taxAmount,            // ← nominal pajak
                    'total_amount'      => $totalAmount,          // ← subtotal + pajak
                    'created_name'      => optional(auth()->user())->name ?? optional($cust->user)->name,
                    'status'            => $statusPayment,
                    'due_date'          => $dueDate,
                    'payment_time'      => $paidAt
                ]);

                if ($billing->wasRecentlyCreated) {
                    $created++;
                }
            }
        });

        return $created;
    }

    public function calculateCustomerDiscount(Customer $customer, string $periodeName): float
    {
        $discount = (float) (optional($customer->member)->discount ?? 0);

        $isEligible =
            ((int) $customer->billing_option === 1) || // selalu
            ((int) $customer->billing_option === 2 && (string) $customer->billing_period === (string) $periodeName);

        return $isEligible ? $discount : 0.0;
    }

    public function calculatePackageDiscount($subDuration, float $basePrice): float
    {
        if (!$subDuration || $subDuration->discount_type === 'none') return 0.0;

        if ($subDuration->discount_type === 'percent') {
            // Pastikan discount_value formatnya 10 = 10% (bukan 0.1)
            return round($basePrice * ((float) $subDuration->discount_value / 100.0), 0);
        }

        if ($subDuration->discount_type === 'amount') {
            return round((float) $subDuration->discount_value, 0);
        }

        return 0.0;
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
