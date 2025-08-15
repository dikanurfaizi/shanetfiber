<?php

namespace App\Services\Customers;

use App\Models\Customer;
use App\Models\Billing\BillingPeriode;
use Illuminate\Support\Str;
use App\Helpers\LogHelper;
use Illuminate\Support\Carbon;

class CustomerBillingService
{
    public function createInitialBilling(Customer $customer, BillingPeriode $periode, string $traceId): void
    {
        try {
            $customer->load(['paket', 'member']);

            $basePrice = $customer->paket->price;
            $discount = $customer->member->discount ?? 0;
            $totalPrice = max(0, $basePrice - $discount);

            $billing = $customer->billing()->create([
                'billing_periode_id' => $periode->id,
                'slug' => Str::slug($customer->id . '-' . Str::random(4)),
                'billing_number' => $this->generateBillingNumber($customer, $periode, $traceId),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'paket_name' => $customer->paket->name,
                'paket_price' => $totalPrice,
                // 'created_name' => auth()->user()->name ?? 'Ridwan Mansyur',
                // 'teller_name' => auth()->user()->name ?? 'Ridwan Mansyur',
                'created_name' => auth()->user()->name ?? $customer->user->name,
                'teller_name' => auth()->user()->name ?? $customer->user->name,
                'status' => 'BL',
                'due_date' => Carbon::now()->startOfMonth()->addDays(14)
            ]);

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'billing_id' => $billing->id,
                    'periode' => $periode->name,
                    'amount' => $totalPrice
                ],
                200,
                'Successfully created initial billing',
                $traceId
            );
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
