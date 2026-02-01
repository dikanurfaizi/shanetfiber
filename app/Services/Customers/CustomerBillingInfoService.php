<?php

namespace App\Services\Customers;

use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPayment;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;

class CustomerBillingInfoService
{
    public function getCurrentBillingPeriod(string $traceId): string
    {
        try {
            $period = BillingPeriode::latest('created_at')->value('name');

            LogHelper::logInfo(
                null,
                ['period_name' => $period],
                200,
                'Successfully retrieved current billing period',
                $traceId
            );

            return $period;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                ],
                500,
                'Failed to get current billing period',
                $traceId
            );
            throw $e;
        }
    }

    public function getCustomerBillingInfo(Customer $customer, string $traceId): array
    {
        try {
            // 1. Ambil billing terbaru
            $latestBilling = Billing::with([
                'billingSubscription.billingPeriode',
                'billingPayment',
                'customer'
            ])
                ->where('customer_id', $customer->id)
                ->latest('created_at')
                ->first();

            $totalAmount = $latestBilling?->total_amount ?? 0;
            $dueDate     = $latestBilling?->due_date ?? null;
            $taxAmount = $latestBilling?->tax_amount ?? 0;
            if ($latestBilling?->status === 'LS') {
                $billingStatus = 'PAID';
            } elseif ($latestBilling?->status === 'BL') {
                $billingStatus = 'UNPAID';
            } elseif ($latestBilling?->status === 'PL') {
                $billingStatus = 'PAYLATER';
            } else {
                $billingStatus = 'UNBILL';
            }

            // 🔧 DEFAULT VALUES — wajib biar aman!
            $lastPayment = 0;
            $lastPaymentTime = null;

            // 2. Last paid billing
            $lastPaidBilling = Billing::with(['billingPayment'])
                ->where('customer_id', $customer->id)
                ->where('status', 'LS')
                ->whereHas('billingPayment')
                ->orderByDesc(
                    BillingPayment::select('payment_time')
                        ->whereColumn('billing_payments.billing_id', 'billings.id')
                        ->limit(1)
                )
                ->first();

            if (
                $lastPaidBilling
            ) {
                $lastPayment     = $lastPaidBilling->billingPayment->amount_paid;
                $lastPaymentTime = $lastPaidBilling->billingPayment->payment_time;
            }

            // 3. Hitung unpaid (tanpa load semua billing)
            $unpaidSum = Billing::where('customer_id', $customer->id)
                ->whereNotIn('status', ['LS', 'VD'])
                ->sum('total_amount');

            // Build result
            $result = [
                'lastPaymentTime'      => $lastPaymentTime,
                'lastPayment'          => $lastPayment,
                'billingPeriods'       => $latestBilling?->billingSubscription?->billingPeriode?->name,
                'totalAmount'          => $totalAmount,
                'taxAmount'            => $taxAmount,
                'dueDate'              => $dueDate,
                'notYetPaid'           => $unpaidSum,
                'billingStatus'        => $billingStatus,
                'stampText'            => match ($billingStatus) {
                    'PAID'    => 'LUNAS',
                    'UNPAID'  => 'BELUM LUNAS',
                    'PAYLATER' => 'BAYAR NANTI',
                    default   => 'BELUM TAGIHAN',
                },
            ];

            return $result;
        } catch (\Throwable $e) {

            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ],
                    'customer_id' => $customer->id,
                ],
                500,
                'Failed to get customer details',
                $traceId
            );

            throw $e;
        }
    }
}
