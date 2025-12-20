<?php

namespace App\Services\Customers;

use App\Models\Customers\Customer;
use App\Models\Billings\BillingPeriode;
use App\Models\Websystem;
use Illuminate\Support\Str;
use App\Helpers\LogHelper;
use App\Models\Packages\PackageDuration;
use Illuminate\Support\Carbon;
use App\Enums\SubscriptionStatus;
use Illuminate\Support\Facades\DB;

class CustomerBillingService
{
    public function createInitialBilling(Customer $customer, BillingPeriode $periode, string $traceId): void
    {
        $t0 = microtime(true);

        try {
            DB::transaction(function () use ($customer, $periode, $traceId, $t0) {

                // Step 0: Preload relations
                $tick = microtime(true);
                $customer->loadMissing(['customerSubscriptions.package', 'customerMembership', 'user']);
                LogHelper::logInfo(
                    null,
                    [
                        'customer_id' => $customer->id,
                        'periode_id'  => $periode->id,
                        'periode'     => $periode->name,
                    ],
                    200,
                    'Step 0: Preload relations',
                    $traceId,
                    (int) round((microtime(true) - $tick) * 1000)
                );

                // Step 1: Price calculation
                $tick = microtime(true);

                $basePrice       = (float) ($customer->package->price ?? 0);
                $memberDiscount  = (float) ($customer->member->discount ?? 0);

                $subscriptionDuration = PackageDuration::where('package_id', $customer->package->id)
                    ->where('duration_months', 1)
                    ->first();

                if (!$subscriptionDuration) {
                    LogHelper::logError(null, [
                        'package_id'   => $customer->package->id,
                        'duration'   => 1,
                    ], 422, 'Step 1: SubscriptionDuration not found', $traceId);
                    throw new \RuntimeException('SubscriptionDuration (1 month) not found for package.');
                }

                $subscriptionDiscount = match ($subscriptionDuration->discount_type) {
                    'percent' => $basePrice * ((float) $subscriptionDuration->discount_value / 100),
                    'amount'  => (float) $subscriptionDuration->discount_value,
                    default   => 0.0,
                };

                $discountAmount = max(0, $memberDiscount + $subscriptionDiscount);
                $subtotal       = max(0, $basePrice - $discountAmount);

                LogHelper::logInfo(null, [
                    'duration_ms'           => (int) round((microtime(true) - $tick) * 1000),
                    'base_price'            => $basePrice,
                    'member_discount'       => $memberDiscount,
                    'subscription_discount' => $subscriptionDiscount,
                    'discount_amount'       => $discountAmount,
                    'subtotal'              => $subtotal,
                    'subscription_duration' => [
                        'id'     => $subscriptionDuration->id,
                        'months' => $subscriptionDuration->duration_months,
                        'type'   => $subscriptionDuration->discount_type,
                        'value'  => $subscriptionDuration->discount_value,
                    ],
                ], 200, 'Step 1: Price calculated', $traceId);

                // Step 2: Create customer subscription
                $tick = microtime(true);
                $customerSubs = $customer->customerSubscriptions()->create([
                    'customer_id'               => $customer->id,
                    'package_id'                  => $customer->package->id,
                    'subscription_duration_id'  => $subscriptionDuration->id,
                    'status'                    => SubscriptionStatus::ACTIVE->value,
                    'terminated_at'             => null,
                    'start_date'                => Carbon::today(),
                    'end_date'                  => Carbon::today()->addMonth()->day(15),
                    'price_final'               => $subtotal,
                    'discount_type_final'       => $subscriptionDuration->discount_type ?? 'none',
                    'discount_value_final'      => $subscriptionDuration->discount_value ?? 0,
                ]);
                LogHelper::logInfo(null, [
                    'duration_ms'              => (int) round((microtime(true) - $tick) * 1000),
                    'customer_subscription_id' => $customerSubs->id,
                ], 200, 'Step 2: Customer subscription created', $traceId);

                // Step 3: Create billing subscription periode
                $tick = microtime(true);
                $billingSubsPeriod = $customerSubs->billingSubscriptionPeriodes()->create([
                    'customer_subscription_id' => $customerSubs->id,
                    'billing_periode_id'       => $periode->id,
                    'billing_periode'          => $periode->name,
                    'is_paid'                  => false,
                    'is_expired'               => false,
                    'status'                   => 'unpaid',
                ]);
                LogHelper::logInfo(null, [
                    'duration_ms'                     => (int) round((microtime(true) - $tick) * 1000),
                    'billing_subscription_periode_id' => $billingSubsPeriod->id,
                ], 200, 'Step 3: Billing subscription periode created', $traceId);

                // Step 4: Tax calculation
                $tick = microtime(true);
                $tax         = Websystem::firstWhere('slug', 'websystem');
                $taxEnabled  = (bool) ($tax->enable_tax ?? false);
                $taxRate     = (float) ($tax->tax_rate ?? 0);
                $taxAmount   = $taxEnabled ? round($subtotal * ($taxRate / 100), 2) : 0.0;
                $totalAmount = $subtotal + $taxAmount;

                LogHelper::logInfo(null, [
                    'duration_ms' => (int) round((microtime(true) - $tick) * 1000),
                    'tax_enabled' => $taxEnabled,
                    'tax_rate'    => $taxRate,
                    'tax_amount'  => $taxAmount,
                    'total_amount' => $totalAmount,
                ], 200, 'Step 4: Tax computed', $traceId);

                // Step 5: Create billing
                $tick = microtime(true);
                $billing = $customer->billing()->create([
                    'billing_subscription_id' => $billingSubsPeriod->id,
                    'slug'                    => Str::slug($customer->id . '-' . Str::random(4)),
                    'billing_number'          => $this->generateBillingNumber($customer, $periode, $traceId),
                    'customer_id'             => $customer->id,
                    'customer_name'           => $customer->name,
                    'customer_address'        => $customer->address,
                    'base_price'              => $basePrice,
                    'discount_amount'         => $discountAmount,
                    'tax_amount'              => $taxAmount,
                    'total_amount'            => $totalAmount,
                    'created_name'            => auth()->user()->name ?? $customer->user->name,
                    'teller_name'             => auth()->user()->name ?? $customer->user->name,
                    'status'                  => 'BL',
                    'due_date'                => Carbon::now()->addMonth()->day(15),
                ]);
                LogHelper::logInfo(null, [
                    'duration_ms' => (int) round((microtime(true) - $tick) * 1000),
                    'billing_id'  => $billing->id,
                ], 200, 'Step 5: Billing created', $traceId);

                // Step 6: Done
                LogHelper::logInfo(null, [
                    'total_duration_ms' => (int) round((microtime(true) - $t0) * 1000),
                    'customer_id'       => $customer->id,
                    'billing_id'        => $billing->id,
                    'periode'           => $periode->name,
                    'amount'            => $totalAmount,
                ], 200, 'Step 6: Successfully created initial billing', $traceId);
            });
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'total_duration_ms' => (int) round((microtime(true) - $t0) * 1000),
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ],
                    'customer_id' => $customer->id ?? null,
                    'periode_id'  => $periode->id ?? null,
                ],
                500,
                'Failed to create initial billing',
                $traceId
            );
            throw $e;
        }
    }

    private function generateBillingNumber(Customer $customer, BillingPeriode $periode, string $traceId): string
    {
        try {
            $month = $periode->created_at->format('m');
            $year = $periode->created_at->format('Y');

            $billingNumber = Str::slug(implode('-', [
                $customer->id,
                Str::random(4),
                $month,
                $year
            ]));

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'billing_number' => $billingNumber
                ],
                200,
                'Successfully generated billing number',
                $traceId
            );

            return $billingNumber;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'periode_id' => $periode->id
                ],
                500,
                'Failed to generate billing number',
                $traceId
            );
            throw $e;
        }
    }
}
