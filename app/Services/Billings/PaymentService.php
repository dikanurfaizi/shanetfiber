<?php

namespace App\Services\Billings;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;
use RuntimeException;
use App\Helpers\LogHelper;
use App\Models\Billing\Billing;
use App\Models\Customer;
use App\Models\Receipt;
use App\Services\Billings\PaymentProcessingService;

class PaymentService
{
    private PaymentProcessingService $paymentProcessingService;

    public function __construct(PaymentProcessingService $paymentProcessingService)
    {
        $this->paymentProcessingService = $paymentProcessingService;
    }

    public function processPayment(Billing $billing, array $paymentData, string $traceId): array
    {
        LogHelper::logInfo('Payment', [
            'billing_id' => $billing->id,
            'customer_id' => $billing->customer_id,
            'trace_id' => $traceId,
            'payment_method' => $paymentData['payment_method'] ?? null,
        ], 100, 'START payment processing', $traceId);

        try {

            DB::beginTransaction();

            // === Step 1 : Update Billing ===
            LogHelper::logInfo('Payment', [
                'billing_id' => $billing->id,
                'step' => 'update_billing'
            ], 200, 'Step 1 — Update billing', $traceId);

            $payment_time = now();

            $billing->update([
                'payment_time' => $payment_time,
                'teller_name' => auth()->user()->name ?? $billing?->customer?->user?->name,
                'status' => 'LS',
            ]);

            // === Step 2 : Billing Subscription ===
            LogHelper::logInfo('Payment', [
                'billing_id' => $billing->id,
                'step' => 'update_subscription'
            ], 200, 'Step 2 — Update billing subscription', $traceId);

            $billing->billingSubscription->update([
                'is_paid' => true,
                'paid_at' => $payment_time,
                'status' => 'paid',
            ]);

            // === Step 3 : Create Receipt ===
            LogHelper::logInfo('Payment', [
                'billing_id' => $billing->id,
                'step' => 'create_receipt'
            ], 200, 'Step 3 — Creating receipt', $traceId);

            $receipt = $this->createReceipt($billing, $paymentData, $traceId);

            if (!$receipt) {
                throw new RuntimeException("Receipt null for billing_id {$billing->id}");
            }

            // === Step 4 : Update Billing with receipt_id ===
            LogHelper::logInfo('Payment', [
                'billing_id' => $billing->id,
                'step' => 'update_billing_receipt'
            ], 200, 'Step 4 — Update billing.receipt_id', $traceId);

            $billing->update(['receipt_id' => $receipt->id]);

            // === Step 5 : Membership update (conditional) ===
            $shouldUpdateMembership =
                $billing->customer->billing_option == 2 &&
                $billing->billingSubscription->billing_periode === $billing->customer->billing_period;

            if ($shouldUpdateMembership) {
                LogHelper::logInfo('Payment', [
                    'billing_id' => $billing->id,
                    'customer_id' => $billing->customer_id,
                    'step' => 'update_membership'
                ], 200, 'Step 5 — Updating membership', $traceId);

                $billing->customer()->update([
                    'billing_option' => '0',
                    'billing_period' => null,
                    'member_id' => null,
                    'discount' => '0',
                ]);
            } else {
                LogHelper::logInfo('Payment', [
                    'billing_id' => $billing->id,
                    'step' => 'update_membership_skip'
                ], 100, 'Step 5 — Membership update skipped', $traceId);
            }

            // === Commit ===
            DB::commit();

            LogHelper::logInfo('Payment', [
                'billing_id' => $billing->id,
                'receipt_id' => $receipt->id,
                'trace_id' => $traceId
            ], 200, 'COMMIT successful', $traceId);

            // === Step 6 : External Actions ===
            if (config('services.shanetfiber.allow_mikrotik_actions')) {

                LogHelper::logInfo('Payment', [
                    'billing_id' => $billing->id,
                    'step' => 'payment_actions'
                ], 200, 'Step 6 — Processing external actions', $traceId);

                $this->paymentProcessingService->processPaymentActions($billing, $traceId);
            }

            return [
                'receipt' => $receipt,
                'trace_id' => $traceId,
            ];
        } catch (Throwable $e) {

            DB::rollBack();

            LogHelper::logError(null, [
                'billing_id' => $billing->id,
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500, 'ERROR during payment', $traceId);

            throw $e;
        }
    }

    private function createReceipt(Billing $billing, array $paymentData, string $traceId): ?Receipt
    {
        $startTime = microtime(true);
        try {
            $customer = $billing->customer;

            LogHelper::logInfo('CreateReceipt', [
                'billing_id' => $billing->id,
                'customer_id' => $customer->id,
                'payment_data' => $paymentData,
            ], 200, 'Starting receipt creation', $traceId);

            $discountData = $this->calculateDiscount($customer, $billing, $traceId);

            $receiptNumber = $this->generateReceiptNumber($traceId);

            $receipt = Receipt::create([
                'slug' => $billing->slug,
                'periode' => $billing->billingSubscription->billing_periode,
                'number' => $receiptNumber,
                'billing_number' => $billing->billing_number,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_address' => $customer->address,
                'package_name' => $customer->package->name,
                'package_price' => $customer->package->price,
                'discount' => $billing->discount_amount,
                'price_after_discount' => $billing->total_amount,
                'member_type' => $discountData['member_type'],
                'created_name' => auth()->user()->name,
                'teller_name' => auth()->user()->name,
                'payment_time' => Carbon::now(),
                'payment_methode' => $paymentData['payment_method'],
                'bank_name' => $paymentData['bank_name'] ?? null,
                'account_name' => $paymentData['account_name'] ?? null,
                'account_number' => $paymentData['account_number'] ?? null,
                'deposit_bank_name' => $paymentData['deposit_bank_name'] ?? null,
                'deposit_account_name' => $paymentData['deposit_account_name'] ?? null,
                'deposit_account_number' => $paymentData['deposit_account_number'] ?? null,
                'reference_id' => $paymentData['reference_id'] ?? null,
                'tax' => $billing->tax,
            ]);

            $executionTime = round(microtime(true) - $startTime, 3);

            LogHelper::logInfo('CreateReceipt', [
                'receipt_id' => $receipt->id,
                'receipt_number' => $receipt->number,
                'execution_seconds' => $executionTime,
            ], 200, 'Receipt created successfully', $traceId);

            return $receipt;
        } catch (\Throwable $e) {
            LogHelper::logError('CreateReceipt', [
                'billing_id' => $billing->id ?? null,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to create receipt', $traceId);

            return null;
        }
    }

    private function generateReceiptNumber(string $traceId): string
    {
        try {
            $lastReceipt = Receipt::latest('created_at')->value('number');

            LogHelper::logInfo('GenerateReceiptNumber', [
                'last_receipt_number' => $lastReceipt,
            ], 200, 'Generating new receipt number', $traceId);

            if ($lastReceipt && str_starts_with($lastReceipt, 'KWT-')) {
                $parts = explode('-', $lastReceipt);
                $number = (int) $parts[1] + 1;
            } else {
                $number = 1;
            }

            $newNumber = 'KWT-' . sprintf('%05d', $number) . '-' .
                Carbon::now()->format('F') . '-' . Carbon::now()->format('Y');

            LogHelper::logInfo('GenerateReceiptNumber', [
                'generated_receipt_number' => $newNumber,
            ], 200, 'Receipt number generated successfully', $traceId);

            return $newNumber;
        } catch (\Throwable $e) {
            LogHelper::logError('GenerateReceiptNumber', [
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to generate receipt number', $traceId);

            // fallback kalau gagal
            return 'KWT-ERROR-' . Carbon::now()->timestamp;
        }
    }

    private function calculateDiscount(Customer $customer, Billing $billing, string $traceId): array
    {
        try {
            $discount = 0;
            $priceAfterDiscount = $customer->package->price;
            $memberType = 'Non Member / Tidak Ada';

            // Logging awal sebelum hitung
            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'billing_id' => $billing->id,
                'billing_option' => $customer->billing_option,
                'billing_period' => $customer->billing_period,
                'package_price' => $customer->package->price,
            ], 200, 'Starting discount calculation', $traceId);

            if ($customer->billing_option == 1) {
                $discount = optional($customer->member)->discount ?? 0;
                $priceAfterDiscount = $customer->package->price - $discount;
                $memberType = optional($customer->member)->type;
            } elseif (
                $customer->billing_option == 2 &&
                $billing->billingSubscription->billing_periode === $customer->billing_period
            ) {
                $discount = optional($customer->member)->discount ?? 0;
                $priceAfterDiscount = $customer->package->price - $discount;
                $memberType = optional($customer->member)->type;
            }

            $result = [
                'discount' => $discount,
                'price_after_discount' => $priceAfterDiscount,
                'member_type' => $memberType,
            ];

            // Logging hasil
            LogHelper::logInfo(null, [
                'result' => $result,
            ], 200, 'Discount calculation completed', $traceId);

            return $result;
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'customer_id' => $customer->id ?? null,
                'billing_id' => $billing->id ?? null,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to calculate discount', $traceId);

            // fallback return
            return [
                'discount' => 0,
                'price_after_discount' => $customer->package->price ?? 0,
                'member_type' => 'Unknown',
            ];
        }
    }
}
