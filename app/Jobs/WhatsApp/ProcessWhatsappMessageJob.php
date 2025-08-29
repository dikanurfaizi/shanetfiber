<?php

namespace App\Jobs\WhatsApp;

use App\DataTransferObjects\WhatsappJobDto;
use App\Models\Billing\Billing;
use App\Models\Receipt;
use App\Helpers\LogHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Enums\WhatsAppAction;
use App\Services\WhatsappMessageService;
use Illuminate\Support\Carbon;

class ProcessWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private WhatsappJobDto $jobData
    ) {}

    public function handle(WhatsappMessageService $whatsappService)
    {
        $this->logSuccess(
            ['action' => $this->jobData->action, 'billing_id' => $this->jobData->billingId ?? null],
            'Start processing WhatsApp job'
        );

        try {
            match ($this->jobData->action) {
                WhatsAppAction::ACTIVATION->value      => $this->handleActivation($whatsappService),
                WhatsAppAction::PAYLATER->value        => $this->handlePayment($whatsappService),
                WhatsAppAction::TRANSFER->value        => $this->handlePayment($whatsappService),
                WhatsAppAction::CASH->value            => $this->handlePayment($whatsappService),
                WhatsAppAction::PAYMENT_GATEWAY->value => $this->handlePayment($whatsappService),
                WhatsAppAction::UNPAYMENT->value       => $this->handleUnpayment($whatsappService),
                WhatsAppAction::CREATE_NEW_BILL->value => $this->handleCreateNewBill($whatsappService),
                WhatsAppAction::SEND_INFO_BILLING->value => $this->handleSendInfoBilling($whatsappService),
                WhatsAppAction::CREATE_ORDER->value    => $this->handleCreateOrder($whatsappService),
                WhatsAppAction::CHANGES_PACKAGE->value => $this->handleChangesPackage($whatsappService),
                default                                => $this->handleUnknownAction()
            };

            $this->logSuccess(
                ['action' => $this->jobData->action, 'billing_id' => $this->jobData->billingId ?? null],
                'Finish processing WhatsApp job'
            );
        } catch (\Throwable $e) {
            $this->logError(
                [
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                    'action' => $this->jobData->action,
                    'customer_id' => $this->jobData->customerId ?? null,
                    'billing_id' => $this->jobData->billingId ?? null,
                    'receipt_id' => $this->jobData->receiptId ?? null
                ],
                '500',
                'Failed processing WhatsApp job'
            );

            // lempar ulang biar Laravel Queue tahu job gagal
            throw $e;
        }
    }

    private function handleActivation(WhatsappMessageService $whatsappService): void
    {
        $whatsappService->activation($this->jobData->customerId, $this->jobData->action, $this->jobData->traceId);
        $this->logSuccess(
            ['customer_id' => $this->jobData->customerId, 'action' => $this->jobData->action],
            "The activation message has entered the delivery queue"
        );
    }

    private function handlePayment(WhatsappMessageService $whatsappService): void
    {
        $billing = Billing::find($this->jobData->billingId);

        if ($this->jobData->action === WhatsappAction::PAYLATER->value) {
            $this->processPayLater($whatsappService, $billing);
            return;
        }

        $this->processRegularPayment($whatsappService, $billing);
    }

    private function processPayLater(WhatsappMessageService $whatsappService, ?Billing $billing): void
    {
        if (!$billing) {
            $this->logBillingNotFound();
            return;
        }

        try {
            $payLaterDate = Carbon::parse($billing->pay_later);
        } catch (\Exception $e) {
            $this->logError([
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'billing_id' => $billing->id,
                'pay_later' => $billing->pay_later,
                'action' => $this->jobData->action
            ], '500', "Invalid pay_later date format");
            return;
        }

        $receipt = $this->createDummyReceipt(
            $billing,
            WhatsappAction::PAYLATER->value,
            $payLaterDate
        );

        $whatsappService->payment_paket($billing, $receipt, $this->jobData->action, $this->jobData->traceId);

        $this->logSuccess([
            'billing_id' => $billing->id,
            'receipt_id' => 'virtual',
            'customer_id' => $billing->customer_id,
            'amount' => $billing->total_amount,
            'pay_later_date' => $billing->pay_later,
            'action' => $this->jobData->action
        ], "Paylater message has been entered into the sending queue");
    }

    private function processRegularPayment(WhatsappMessageService $whatsappService, ?Billing $billing): void
    {
        if (!$billing || !$this->jobData->receiptId) {
            $this->logInvalidPaymentData();
            return;
        }

        $receipt = Receipt::find($this->jobData->receiptId);

        if (!$receipt) {
            $this->logReceiptNotFound();
            return;
        }

        $whatsappService->payment_paket($billing, $receipt, $this->jobData->action, $this->jobData->traceId);
        $this->logSuccess([
            'billing_id' => $billing->id,
            'receipt_id' => $receipt->id,
            'customer_id' => $billing->customer_id,
            'amount' => $billing->total_amount ?? 0
        ], "Payment message has been entered into the sending queue");
    }

    private function handleUnpayment(WhatsappMessageService $whatsappService)
    {
        $billing = Billing::find($this->jobData->billingId);

        // 1. Mencari receipt dengan prioritas:
        $receipt = $this->resolveReceiptForUnpayment();

        if ($billing) {
            // 2. Jika receipt tetap null, buat dummy receipt
            $receipt = $receipt ?? $this->createDummyReceipt($billing, 'unpayment');;

            $whatsappService->payment_paket($billing, $receipt, $this->jobData->action, $this->jobData->traceId);

            $this->logSuccess([
                'billing_id' => $billing->id,
                'receipt_id' => $receipt->id,
                'customer_id' => $billing->customer_id,
                'amount' => $receipt->total_amount ?? 0,
            ], "Unpayment message successfully entered into the send queue");
        } else {
            $this->logWarning([
                'billing_id' => $this->jobData->billingId,
                'receipt_id' => $this->jobData->receiptId,
                'action' => $this->jobData->action
            ], '404', "Billing data not found");
        }
    }

    /**
     * Resolve receipt data with fallback logic
     */
    private function resolveReceiptForUnpayment(): ?Receipt
    {
        try {
            // 1. Cek dari receiptData jika ada
            if (isset($this->receiptData['id']) && $this->jobData->receiptData['id'] !== null) {
                return new Receipt($this->jobData->receiptData);
            }

            // 2. Cek dari receiptId (termasuk yang ter-soft delete)
            if ($this->jobData->receiptId) {
                return Receipt::withTrashed()->find($this->jobData->receiptId);
            }

            // 3. Coba cari dari billing_id jika tersedia
            if ($this->jobData->billingId) {
                $billing = Billing::with('receipt')->find($this->jobData->billingId);
                return $billing ? $billing->receipt()->withTrashed()->first() : null;
            }

            return null;
        } catch (\Exception $e) {
            $this->logError([
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'action' => $this->jobData->action,
                'trace_id' => $this->jobData->traceId
            ], '500', "Failed to resolve receipt for unpayment");
            return null;
        }
    }

    /**
     * Create minimal dummy receipt for notifications
     */
    private function createDummyReceipt(Billing $billing, string $action = 'unpayment', ?Carbon $payLaterDate = null): Receipt
    {
        $baseData = [
            'billing_number' => $billing->billing_number,
            'customer_id' => $billing->customer_id,
            'customer_name' => $billing->customer_name,
            'paket_name' => $billing->paket?->name ?? 'Unknown Package',
            'paket_price' => $billing->total_amount,
            'payment_time' => now(),
            'payment_methode' => $action,
            'created_name' => 'system',
            'teller_name' => 'system',
            'is_dummy' => true
        ];

        // Tambahkan data khusus paylater jika ada
        if ($action === 'paylater' && $payLaterDate) {
            $baseData['pay_later_date'] = $payLaterDate;
        }

        try {
            return new Receipt($baseData);
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'error' => $e->getMessage(),
                'trace_id' => $this->jobData->traceId
            ], 500, "Failed to create dummy receipt for {$action}");

            // Fallback minimal data
            return new Receipt([
                'billing_number' => $billing->billing_number,
                'customer_id' => $billing->customer_id,
                'customer_name' => $billing->customer_name,
                'payment_time' => now(),
                'payment_methode' => $action,
                'is_dummy' => true
            ]);
        }
    }

    private function handleCreateNewBill(WhatsappMessageService $whatsappService)
    {
        $whatsappService->isBillingOpenPeriod($this->jobData->startDate, $this->jobData->endDate, $this->jobData->action, $this->jobData->traceId);
        $this->logSuccess(['billing_id' => $this->jobData->billingId, 'action' => $this->jobData->action], "The whatsApp search and delivery process has been received");
    }

    private function handleSendInfoBilling(WhatsappMessageService $whatsappService)
    {
        $whatsappService->sendPaymentReminders($this->jobData->traceId);
        $this->logSuccess([], 'Send payment reminder successfully proceed and delivery');
    }

    private function handleCreateOrder(WhatsappMessageService $whatsappService)
    {
        $whatsappService->createOrder($this->jobData->billingId, $this->jobData->orderId, $this->jobData->action, $this->jobData->traceId);
        $this->logSuccess([], 'Send payment reminder successfully proceed and delivery');
    }

    private function handleUnknownAction()
    {
        $this->logError([], '404', "There is unknown action, please check your code again");
    }

    private function handleChangesPackage(WhatsappMessageService $whatsappService)
    {
        $whatsappService->changesPackage($this->jobData->customerId, $this->jobData->oldPaketName, $this->jobData->oldPaketPrice, $this->jobData->traceId);
        $this->logSuccess([], 'Send changes package information successfully proceed and delivery');
    }

    private function logSuccess(array $context, string $message): void
    {
        LogHelper::logInfo(null, $context, 200, $message, $this->jobData->traceId);
    }

    private function logWarning(array $context, string $errorCode, string $message): void
    {
        LogHelper::logWarning(null, $context, $errorCode, $message, $this->jobData->traceId);
    }

    private function logError(array $context, string $errorCode, string $message): void
    {
        LogHelper::logError(null, $context, $errorCode, $message, $this->jobData->traceId);
    }

    private function logBillingNotFound(): void
    {
        $this->logWarning(['billing_id' => $this->jobData->billingId, 'action' => $this->jobData->action], '404', "Billing data not found");
    }

    private function logInvalidPaymentData(): void
    {
        $this->logError(['billing_id' => $this->jobData->billingId, 'action' => $this->jobData->action], '404', "Invalid Payment Data");
    }

    private function logReceiptNotFound(): void
    {
        $this->logWarning(['billing_id' => $this->jobData->billingId, 'action' => $this->jobData->action], '404', "Receipt data not found");
    }
}
