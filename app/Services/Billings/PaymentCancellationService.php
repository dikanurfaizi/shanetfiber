<?php

namespace App\Services\Billings;

use App\Models\Billing\Billing;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\Order;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Models\Billing\BillingPeriode;
use App\Jobs\CancelPaymentBeforeDueDateJob;
use App\Jobs\CancelPaymentAfterDueDateJob;
use App\Jobs\CancelPaymentIpStaticBeforeDueDateJob;
use App\Jobs\CancelPaymentIpStaticAfterDueDateJob;
use App\Jobs\WhatsApp\ProcessWhatsappMessageJob;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Helpers\LogHelper;
use App\DataTransferObjects\WhatsappJobDto;
use App\Enums\WhatsAppAction;

class PaymentCancellationService
{
    public function cancelPayment(Billing $billing): array
    {
        $traceId = (string) Str::uuid();
        $customer = $billing->customer;

        try {
            // 1. Proses pembatalan di Mikrotik
            $this->processMikrotikCancellation($customer, $traceId);

            // 2. Hapus receipt jika ada
            $originalReceipt = $this->handleReceipt($billing);

            LogHelper::logInfo(null, ['original_receipt' => $originalReceipt], 200, 'Original Receipt', $traceId);

            // 3. Update status billing
            $this->updateBillingStatus($billing, $traceId);

            // 4. Update order terkait
            $this->updateRelatedOrders($billing, $traceId);

            // 5. Update billing subscriptions periode
            $this->updateBillingSubscriptionPeriodes($billing);

            // 5. Kirim notifikasi WhatsApp
            $this->sendCancellationNotification($billing, $originalReceipt, $traceId);

            return [
                'success' => true,
                'message' => 'Cancel payment successfully.',
                'trace_id' => $traceId
            ];
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['error' => $e->getMessage()],
                500,
                'Payment cancellation failed',
                $traceId
            );

            return [
                'success' => false,
                'message' => 'Cancel payment failed: ' . $e->getMessage(),
                'trace_id' => $traceId
            ];
        }
    }

    private function processMikrotikCancellation(Customer $customer, string $traceId)
    {
        $router = Mikrotik::find($customer->mikrotik_id);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

        if (!$router || !$autoIsolir) {
            return;
        }

        $day = Carbon::now()->format('d');
        $dueDate = $this->getDueDate($autoIsolir, $customer, $traceId);
        $commentUnpayment = $this->getCommentUnpayment($autoIsolir, $customer, $traceId);

        if ($customer->internet_service == 'ppp') {
            $this->cancelPppPayment($router, $customer, $day, $dueDate, $commentUnpayment, $autoIsolir, $traceId);
        } elseif ($customer->internet_service == 'ip_static') {
            $this->cancelIpStaticPayment($router, $customer, $day, $dueDate, $commentUnpayment, $autoIsolir, $traceId);
        }
    }

    private function getDueDate(AutoIsolir $autoIsolir, Customer $customer, string $traceId): ?string
    {
        $startTime = microtime(true);
        try {
            LogHelper::logInfo('GetDueDate', [
                'trace_id' => $traceId,
                'auto_isolir_id' => $autoIsolir->id ?? null,
                'customer_id' => $customer->id ?? null,
                'activation_date_flag' => $autoIsolir->activation_date
            ], 200, 'Starting getDueDate process', $traceId);

            if ($autoIsolir->activation_date == 'false') {
                $dueDate = $autoIsolir->due_date;
            } else {
                $dueDate = Carbon::parse($customer->activation_date)->format('d');
            }

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo('GetDueDate', [
                'trace_id' => $traceId,
                'due_date' => $dueDate,
                'execution_seconds' => $executionTime
            ], 200, 'Successfully retrieved due date', $traceId);

            return $dueDate;
        } catch (\Throwable $e) {
            LogHelper::logError('GetDueDate', [
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to get due date', $traceId);
            return null;
        }
    }

    private function getCommentUnpayment(AutoIsolir $autoIsolir, Customer $customer, string $traceId): ?string
    {
        $startTime = microtime(true);
        try {
            LogHelper::logInfo('GetCommentUnpayment', [
                'trace_id' => $traceId,
                'auto_isolir_id' => $autoIsolir->id ?? null,
                'customer_id' => $customer->id ?? null,
                'activation_date_flag' => $autoIsolir->activation_date
            ], 200, 'Starting getCommentUnpayment process', $traceId);

            if ($autoIsolir->activation_date == 'false') {
                $comment = $autoIsolir->comment_unpayment;
            } else {
                $date = Carbon::parse($customer->activation_date)->format('d');
                $periodeName = BillingPeriode::latest('created_at')->value('name');

                if (!$periodeName) {
                    throw new \RuntimeException('Billing periode name not found.');
                }

                $month = Carbon::parse($periodeName)->format('m');
                $year = Carbon::parse($periodeName)->format('Y');

                $comment = $autoIsolir->comment_unpayment . '_' . $date . '_' . $month . '_' . $year;
            }

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo('GetCommentUnpayment', [
                'trace_id' => $traceId,
                'comment_unpayment' => $comment,
                'execution_seconds' => $executionTime
            ], 200, 'Successfully retrieved comment unpayment', $traceId);

            return $comment;
        } catch (\Throwable $e) {
            LogHelper::logError('GetCommentUnpayment', [
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to get comment unpayment', $traceId);
            return null;
        }
    }

    private function cancelPppPayment(
        Mikrotik $router,
        Customer $customer,
        string $day,
        string $dueDate,
        string $commentUnpayment,
        AutoIsolir $autoIsolir,
        string $traceId
    ) {
        $startTime = microtime(true);
        try {
            LogHelper::logInfo('CancelPppPayment', [
                'trace_id' => $traceId,
                'customer_id' => $customer->id ?? null,
                'router_id' => $router->id ?? null,
                'day' => $day,
                'due_date' => $dueDate
            ], 200, 'Starting cancel PPP payment process', $traceId);

            if ($day <= $dueDate) {
                dispatch(new CancelPaymentBeforeDueDateJob(
                    $router,
                    $commentUnpayment,
                    $customer->secret_id,
                    $traceId
                ))->onQueue('default');

                $message = "Successfully dispatched cancel PPP payment before due date job";
            } else {
                dispatch(new CancelPaymentAfterDueDateJob(
                    $router,
                    $commentUnpayment,
                    $autoIsolir->profile_id,
                    $customer->secret_id,
                    $customer->username,
                    $traceId
                ))->onQueue('default');

                $message = "Successfully dispatched cancel PPP payment after due date job";
            }

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo('CancelPppPayment', [
                'trace_id' => $traceId,
                'execution_seconds' => $executionTime
            ], 200, $message, $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError('CancelPppPayment', [
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to cancel PPP payment', $traceId);
        }
    }

    private function cancelIpStaticPayment(
        Mikrotik $router,
        Customer $customer,
        string $day,
        string $dueDate,
        string $commentUnpayment,
        AutoIsolir $autoIsolir,
        string $traceId
    ) {
        $startTime = microtime(true);
        try {
            LogHelper::logInfo('CancelIpStaticPayment', [
                'trace_id' => $traceId,
                'customer_id' => $customer->id ?? null,
                'router_id' => $router->id ?? null,
                'day' => $day,
                'due_date' => $dueDate
            ], 200, 'Starting cancel IP Static payment process', $traceId);

            $comment = $customer->username . '_' . $commentUnpayment;

            if ($day <= $dueDate) {
                dispatch(new CancelPaymentIpStaticBeforeDueDateJob(
                    $router,
                    $customer->simpleque_id,
                    $commentUnpayment,
                    $traceId
                ))->onQueue('default');

                $message = "Successfully dispatched cancel IP static payment before due date job";
            } else {
                dispatch(new CancelPaymentIpStaticAfterDueDateJob(
                    $router,
                    $autoIsolir->address_list_isolir,
                    $customer->ip_static,
                    $customer->simpleque_id,
                    $comment,
                    $traceId
                ))->onQueue('default');

                $message = "Successfully dispatched cancel IP static payment after due date job";
            }

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo('CancelIpStaticPayment', [
                'trace_id' => $traceId,
                'execution_seconds' => $executionTime
            ], 200, $message, $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError('CancelIpStaticPayment', [
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to cancel IP Static payment', $traceId);
        }
    }

    private function updateBillingStatus(Billing $billing, string $traceId)
    {
        $startTime = microtime(true);
        try {
            LogHelper::logInfo('UpdateBillingStatus', [
                'trace_id' => $traceId,
                'billing_id' => $billing->id ?? null
            ], 200, 'Starting update billing status', $traceId);

            $billing->update([
                'teller_name' => auth()->user()->name,
                'status' => 'BL',
                'receipt_id' => null,
            ]);

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo('UpdateBillingStatus', [
                'trace_id' => $traceId,
                'execution_seconds' => $executionTime
            ], 200, 'Successfully updated billing status', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError('UpdateBillingStatus', [
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to update billing status', $traceId);
        }
    }

    protected function handleReceipt(Billing $billing): ?array
    {
        $billing->loadMissing('receipt');
        $originalReceipt = $billing->receipt;

        if ($originalReceipt) {
            $receiptData = $originalReceipt->toArray();

            try {
                // Soft delete
                $originalReceipt->delete();

                // Optional: update billing agar tidak merujuk ke receipt yang sudah di soft delete
                $billing->update(['receipt_id' => null]);
            } catch (\Exception $e) {
                LogHelper::logError(null, [
                    'billing_id' => $billing->id,
                    'receipt_id' => $originalReceipt->id,
                    'error' => $e->getMessage(),
                ], 500, 'Failed to soft delete original receipt');
                // Return data receipt tetap agar proses lanjut
                return $receiptData;
            }

            LogHelper::logInfo(null, ['receipt_data' => $receiptData], 200, 'Suceesfully get receipt data');

            return $receiptData;
        }

        return null;
    }

    private function updateRelatedOrders(Billing $billing, string $traceId)
    {
        $orders = Order::where('billing_id', $billing->id)->get();

        if ($orders->isNotEmpty()) {
            foreach ($orders as $order) {
                $order->update([
                    'payment_status' => Order::STATUS_CANCELED
                ]);
            }

            LogHelper::logInfo(
                null,
                ['order_count' => $orders->count(), 'billing_id' => $billing->id],
                200,
                "Canceled orders",
                $traceId
            );
        } else {
            LogHelper::logWarning(
                null,
                ['billing_id' => $billing->id],
                404,
                "No orders found",
                $traceId
            );
        }
    }

    private function updateBillingSubscriptionPeriodes($billing): void
    {
        try {
            $billing->loadMissing(['billingSubscription']);

            if (!$billing->billingSubscription) {
                throw new \Exception('Billing subscription not found for billing ID: ' . $billing->id);
            }

            $billing->billingSubscription->update([
                'is_paid' => false,
                'paid_at' => null
            ]);

            LogHelper::logInfo(null, [
                'billing_id' => $billing->id,
                'billing_subscription_id' => $billing->billingSubscription->id ?? null,
                'is_paid' => false,
                'paid_at' => null
            ], 200, 'Billing subscription periodes updated successfully');
        } catch (\Throwable $e) {
            LogHelper::logError($e, [
                'billing_id' => $billing->id ?? null
            ], 500, 'Failed to update billing subscription periodes');
        }
    }

    private function sendCancellationNotification(
        Billing $billing,
        ?array $originalReceipt,
        string $traceId
    ): void {
        try {
            LogHelper::logInfo(null, ['original_receipt' => $originalReceipt], 200, 'Original Receipt', $traceId);
            $jobData = new WhatsappJobDto(
                customerId: (int) $billing->customer_id,
                action: WhatsAppAction::UNPAYMENT->value,
                traceId: (string) $traceId,
                billingId: (int) $billing->id,
                receiptId: null,
                receiptData: $originalReceipt,
                startDate: null,
                endDate: null,
                orderId: null,
                oldPaketName: null,
                oldPaketPrice: null
            );

            ProcessWhatsappMessageJob::dispatch($jobData)->onQueue('whatsapp_single');

            LogHelper::logInfo(null, [
                'billing_id' => $billing->id,
                'customer_id' => $billing->customer_id,
                'trace_id' => $traceId,
                'receipt_data' => $originalReceipt,
            ], 200, 'Cancellation notification sent', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError($e, [
                'billing_id' => $billing->id ?? null,
                'customer_id' => $billing->customer_id ?? null,
                'trace_id' => $traceId,
                'receipt_data' => $originalReceipt,
            ], 500, 'Failed to send cancellation notification', $traceId);
        }
    }
}
