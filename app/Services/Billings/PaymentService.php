<?php

namespace App\Services\Billings;

use App\Models\Billing\Billing;
use App\Models\Customer;
use App\Models\Receipt;
use Carbon\Carbon;
use App\DataTransferObjects\WhatsappJobDto;
use App\Jobs\WhatsApp\ProcessWhatsappMessageJob;
use App\Helpers\LogHelper;
use App\Enums\WhatsAppAction;
use App\Services\Billings\PaymentProcessingService;
use App\Services\Billings\PaymentCancellationService;

class PaymentService
{
    private PaymentProcessingService $paymentProcessingService;
    private PaymentCancellationService $paymentCancellationService;

    public function __construct(PaymentProcessingService $paymentProcessingService, PaymentCancellationService $paymentCancellationService)
    {
        $this->paymentProcessingService = $paymentProcessingService;
        $this->paymentCancellationService = $paymentCancellationService;
    }

    public function processPayment(Billing $billing, array $paymentData, string $traceId): array
    {
        LogHelper::logInfo(null, [
            'billing_id' => $billing->id,
            'customer_id' => $billing->customer_id,
            'payment_data' => $paymentData,
            'trace_id' => $traceId,
        ], 100, 'Start processing payment', $traceId);

        try {
            $this->paymentProcessingService->processPaymentActions($billing, $traceId);
            LogHelper::logInfo(null, ['billing_id' => $billing->id], 200, 'Payment actions processed', $traceId);

            $payment_time = Carbon::now();

            $billing->update([
                'payment_time' => $payment_time,
                'teller_name' => auth()->user()->name ?? $billing?->customer?->user?->name,
                'status' => 'LS',
            ]);
            LogHelper::logInfo(null, [
                'billing_id' => $billing->id,
                'payment_time' => $payment_time,
                'teller_name' => auth()->user()->name ?? $billing?->customer?->user?->name,
                'status' => 'LS'
            ], 200, 'Billing updated with payment info', $traceId);

            $billing->billingSubscription->update([
                'is_paid' => true,
                'paid_at' => $payment_time
            ]);
            LogHelper::logInfo(null, [
                'billing_id' => $billing->id,
                'billing_subscription_is_paid' => true,
                'billing_subscripiton_paid_at' => $payment_time
            ], 200, 'Billing subscription periode updated with payment info', $traceId);

            $receipt = $this->createReceipt($billing, $paymentData, $traceId);
            LogHelper::logInfo(null, ['receipt_id' => $receipt->id], 200, 'Receipt created', $traceId);

            $billing->update(['receipt_id' => $receipt->id]);
            LogHelper::logInfo(null, ['billing_id' => $billing->id, 'receipt_id' => $receipt->id], 200, 'Billing updated with receipt id', $traceId);

            if (
                $billing->customer->billing_option == 2 &&
                $billing->billingSubscription->billing_periode === $billing->customer->billing_period
            ) {
                $this->updateCustomerMembership($billing->customer, $traceId);
            }

            LogHelper::logInfo(null, [
                'periode' => $billing->billingSubscription->billing_periode ?? null,
                'customer_id' => $billing->customer_id,
                'customer_name' => $billing->customer_name,
                'billing_id' => $billing->id,
                'payment_type' => $paymentData['payment_method'] ?? null,
                'receipt_id' => $receipt->id,
            ], 200, 'Payment processed successfully', $traceId);

            return [
                'receipt' => $receipt,
                'trace_id' => $traceId
            ];
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'billing_id' => $billing->id,
                'errorCode' => $e->getCode(),
                'errorMessage' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Payment processing failed', $traceId);
            throw $e;
        }
    }

    private function createReceipt(Billing $billing, array $paymentData, string $traceId): ?Receipt
    {
        $startTime = microtime(true);
        try {
            $customer = $billing->customer;

            LogHelper::logInfo('CreateReceipt', [
                'trace_id' => $traceId,
                'billing_id' => $billing->id,
                'customer_id' => $customer->id,
                'payment_data' => $paymentData
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
                'paket_name' => $customer->paket->name,
                'paket_price' => $customer->paket->price,
                'discount' => $billing->discount_amount,
                'price_after_discount' => $billing->subtotal,
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
                'trace_id' => $traceId,
                'receipt_id' => $receipt->id,
                'receipt_number' => $receipt->number,
                'execution_seconds' => $executionTime
            ], 200, 'Receipt created successfully', $traceId);

            return $receipt;
        } catch (\Throwable $e) {
            LogHelper::logError('CreateReceipt', [
                'trace_id' => $traceId,
                'billing_id' => $billing->id ?? null,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to create receipt', $traceId);

            return null;
        }
    }

    private function generateReceiptNumber(string $traceId): string
    {
        try {
            $lastReceipt = Receipt::latest('created_at')->value('number');

            LogHelper::logInfo('GenerateReceiptNumber', [
                'trace_id' => $traceId,
                'last_receipt_number' => $lastReceipt
            ], 200, 'Generating new receipt number', $traceId);

            if (!$lastReceipt) {
                $number = 1;
            } else {
                $parts = explode('-', $lastReceipt);
                $number = (int)$parts[1] + 1;
            }

            $newNumber = str('KWT-' . sprintf("%05d", $number) . '-' .
                Carbon::now()->format('F') . '-' . Carbon::now()->format('Y'));

            LogHelper::logInfo('GenerateReceiptNumber', [
                'trace_id' => $traceId,
                'generated_receipt_number' => $newNumber
            ], 200, 'Receipt number generated successfully', $traceId);

            return $newNumber;
        } catch (\Throwable $e) {
            LogHelper::logError('GenerateReceiptNumber', [
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to generate receipt number', $traceId);

            // fallback kalau gagal
            return 'KWT-ERROR-' . Carbon::now()->timestamp;
        }
    }

    private function calculateDiscount(Customer $customer, Billing $billing, string $traceId): array
    {
        try {
            $discount = 0;
            $priceAfterDiscount = $customer->paket->price;
            $memberType = 'Non Member / Tidak Ada';

            // Logging awal sebelum hitung
            LogHelper::logInfo('CalculateDiscount', [
                'trace_id' => $traceId,
                'customer_id' => $customer->id,
                'billing_id' => $billing->id,
                'billing_option' => $customer->billing_option,
                'billing_period' => $customer->billing_period,
                'paket_price' => $customer->paket->price
            ], 200, 'Starting discount calculation', $traceId);

            if ($customer->billing_option == 1) {
                $discount = optional($customer->member)->discount ?? 0;
                $priceAfterDiscount = $customer->paket->price - $discount;
                $memberType = optional($customer->member)->type;
            } elseif (
                $customer->billing_option == 2 &&
                $billing->billingSubscription->billing_periode === $customer->billing_period
            ) {
                $discount = optional($customer->member)->discount ?? 0;
                $priceAfterDiscount = $customer->paket->price - $discount;
                $memberType = optional($customer->member)->type;
            }

            $result = [
                'discount' => $discount,
                'price_after_discount' => $priceAfterDiscount,
                'member_type' => $memberType
            ];

            // Logging hasil
            LogHelper::logInfo('CalculateDiscount', [
                'trace_id' => $traceId,
                'result' => $result
            ], 200, 'Discount calculation completed', $traceId);

            return $result;
        } catch (\Throwable $e) {
            LogHelper::logError('CalculateDiscount', [
                'trace_id' => $traceId,
                'customer_id' => $customer->id ?? null,
                'billing_id' => $billing->id ?? null,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to calculate discount', $traceId);

            // fallback return
            return [
                'discount' => 0,
                'price_after_discount' => $customer->paket->price ?? 0,
                'member_type' => 'Unknown'
            ];
        }
    }

    private function updateCustomerMembership(Customer $customer, string $traceId): void
    {
        $customer->update([
            'billing_option' => '0',
            'billing_period' => null,
            'member_id' => null,
            'discount' => '0',
        ]);

        LogHelper::logInfo(null, [
            'customer_id' => $customer->id,
            'billing_option' => '0',
            'billing_period' => null,
            'member_id' => null,
            'discount' => '0'
        ], 200, 'Customer membership updated', $traceId);
    }

    public function cancelPayment(Billing $billing): array
    {
        return $this->paymentCancellationService->cancelPayment($billing);
    }

    public function sendPaymentNotification(
        int $customerId,
        string $paymentType,
        string $traceId,
        int $billingId,
        ?int $receiptId = null
    ): void {
        try {
            // Tentukan action berdasarkan tipe pembayaran
            if ($paymentType === 'transfer') {
                $action = WhatsAppAction::TRANSFER->value;
            } elseif ($paymentType === 'cash') {
                $action = WhatsAppAction::CASH->value;
            } else {
                $action = $paymentType;
            }

            $jobData = new WhatsappJobDto(
                customerId: (int) $customerId,
                action: $action,
                traceId: (string) $traceId,
                billingId: (int) $billingId,
                receiptId: $receiptId,
                receiptData: null,
                startDate: null,
                endDate: null,
                orderId: null,
                oldPaketName: null,
                oldPaketPrice: null
            );

            ProcessWhatsappMessageJob::dispatch($jobData)->onQueue('whatsapp_single');

            LogHelper::logInfo('PaymentNotification', [
                'trace_id' => $traceId,
                'customer_id' => $customerId,
                'billing_id' => $billingId,
                'receipt_id' => $receiptId,
                'payment_type' => $paymentType,
                'action' => $action
            ], 200, 'Payment notification dispatched successfully', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError('PaymentNotification', [
                'trace_id' => $traceId,
                'customer_id' => $customerId,
                'billing_id' => $billingId,
                'receipt_id' => $receiptId,
                'payment_type' => $paymentType,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to dispatch payment notification', $traceId);
        }
    }
}
