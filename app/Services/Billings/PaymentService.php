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
        try {
            $this->paymentProcessingService->processPaymentActions($billing->customer, $traceId);

            $billing->update([
                'payment_time' => Carbon::now(),
                'teller_name' => auth()->user()->name,
                'status' => 'LS',
            ]);

            $receipt = $this->createReceipt($billing, $paymentData);

            $billing->update(['receipt_id' => $receipt->id]);

            $this->updateCustomerMembership($billing->customer, $billing);

            LogHelper::logInfo(
                null,
                [
                    'periode' => $billing->billing_periode->name,
                    'customer_id' => $billing->customer_id,
                    'customer_name' => $billing->customer_name,
                    'billing_id' => $billing->id,
                    'payment_type' => $paymentData['payment_method'],
                    'receipt_id' => $receipt->id,
                ],
                200,
                'Payment processed successfully',
                $traceId
            );

            return [
                'receipt' => $receipt,
                'trace_id' => $traceId
            ];
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()],
                500,
                'Payment processing failed',
                $traceId
            );
            throw $e;
        }
    }

    private function createReceipt(Billing $billing, array $paymentData): Receipt
    {
        $customer = $billing->customer;
        $discountData = $this->calculateDiscount($customer, $billing);

        $receiptNumber = $this->generateReceiptNumber();

        return Receipt::create([
            'slug' => $billing->slug,
            'periode' => $billing->billing_periode->name,
            'number' => $receiptNumber,
            'billing_number' => $billing->billing_number,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_address' => $customer->address,
            'paket_name' => $customer->paket->name,
            'paket_price' => $customer->paket->price,
            'discount' => $discountData['discount'],
            'price_after_discount' => $discountData['price_after_discount'],
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
    }

    private function generateReceiptNumber(): string
    {
        $lastReceipt = Receipt::latest('created_at')->value('number');

        if (!$lastReceipt) {
            $number = 1;
        } else {
            $parts = explode('-', $lastReceipt);
            $number = (int)$parts[1] + 1;
        }

        return str('KWT-' . sprintf("%05d", $number) . '-' .
            Carbon::now()->format('F') . '-' . Carbon::now()->format('Y'));
    }

    private function calculateDiscount(Customer $customer, Billing $billing): array
    {
        $discount = 0;
        $priceAfterDiscount = $customer->paket->price;
        $memberType = 'Non Member / Tidak Ada';

        if ($customer->billing_option == 1) {
            $discount = optional($customer->member)->discount ?? 0;
            $priceAfterDiscount = $customer->paket->price - $discount;
            $memberType = optional($customer->member)->type;
        } elseif (
            $customer->billing_option == 2 &&
            $billing->billing_periode->name === $customer->billing_period
        ) {
            $discount = optional($customer->member)->discount ?? 0;
            $priceAfterDiscount = $customer->paket->price - $discount;
            $memberType = optional($customer->member)->type;
        }

        return [
            'discount' => $discount,
            'price_after_discount' => $priceAfterDiscount,
            'member_type' => $memberType
        ];
    }

    private function updateCustomerMembership(Customer $customer, Billing $billing): void
    {
        if (
            $customer->billing_option == 2 &&
            $billing->billing_periode->name === $customer->billing_period
        ) {
            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id' => null,
                'discount' => '0',
            ]);
        } else {
            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id' => null,
                'discount' => '0',
            ]);
        }
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
        if ($paymentType === 'transfer') {
            $action = WhatsAppAction::TRANSFER->value;
        } elseif ($paymentType === 'cash') {
            $action = WhatsAppAction::CASH->value;
        } else {
            $action = $paymentType;
        }

        $jobData = new WhatsappJobDto(
            customerId: (int)$customerId,
            action: $action,
            traceId: (string)$traceId,
            billingId: (int)$billingId,
            receiptId: (int)$receiptId,
            receiptData: null,
            startDate: null,
            endDate: null,
            orderId: null
        );

        ProcessWhatsappMessageJob::dispatch($jobData)->onQueue('default');
    }
}
