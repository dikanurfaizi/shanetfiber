<?php

namespace App\Services\Billings;

use App\Enums\OrderStatus;
use App\Events\PaymentCancelled;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Jobs\CancelPaymentAfterDueDateJob;
use App\Jobs\CancelPaymentBeforeDueDateJob;
use App\Jobs\CancelPaymentIpStaticAfterDueDateJob;
use App\Jobs\CancelPaymentIpStaticBeforeDueDateJob;
use App\Models\AutoIsolir;
use App\Models\Billings\Order;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPayment;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentCancellationService
{
    /**
     * Orkestrasi pembatalan pembayaran (unpayment) secara transactional.
     */
    public function cancelPayment(BillingPayment $payment, string $traceId): array
    {
        $timer = new ExecutionTimer();
        $mikrotikResult = [
            'status' => 'skipped',
            'reason' => 'mikrotik_action_disabled',
        ];
        $originalReceipt = null;

        $billingId = $payment->billing_id;
        $customerId = $payment->billing?->customer_id;

        try {
            // LOG INTERNAL: Parameter pertama berupa string nama aksi/method
            LogHelper::logInfo('CancelPayment', [
                'billing_id' => $billingId,
                'payment_id' => $payment->id,
                'customer_id' => $customerId,
            ], 200, 'Starting transaction for payment cancellation.', $traceId, $timer->lap());

            DB::transaction(function () use ($payment, $traceId, &$originalReceipt, $timer) {
                $payment->lockForUpdate();
                $billing = $payment->billing()->lockForUpdate()->firstOrFail();

                // Proses hapus receipt & payment
                $originalReceipt = $this->handleReceipt($payment, $traceId);

                $this->updateBillingStatus($billing, $traceId);
                $this->updateRelatedOrders($billing, $traceId);
            });

            if (config('services.shanetfiber.allow_mikrotik_actions')) {
                LogHelper::logInfo('CancelPayment', [
                    'customer_id' => $customerId,
                ], 200, 'Mikrotik action is enabled. Processing Mikrotik cancellation.', $traceId, $timer->lap());

                $mikrotikResult = $this->processMikrotikCancellation($payment->billing->customer, $traceId);
            }

            LogHelper::logInfo('CancelPayment', [
                'billing_id' => $billingId,
                'payment_id' => $payment->id,
                'receipt_id' => $originalReceipt['id'] ?? null,
                'mikrotik_result' => $mikrotikResult,
            ], 200, 'Payment cancellation processed successfully.', $traceId, $timer->lap());

            return [
                'success' => true,
                'message' => 'Pembatalan pembayaran untuk ' . ($payment->billing->customer->name ?? 'Pelanggan') . ' berhasil diproses.',
                'originalReceipt' => $originalReceipt,
                'mikrotik' => $mikrotikResult,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            LogHelper::logError('CancelPayment', [
                'billing_id' => $billingId,
                'payment_id' => $payment->id,
                'customer_id' => $customerId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to cancel payment. Transaction rolled back.', $traceId, $timer->lap());

            return [
                'success' => false,
                'message' => 'Gagal melakukan unpayment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Menyiapkan pembatalan di Mikrotik: hitung due date/comment lalu dispatch job ke queue.
     */
    public function processMikrotikCancellation(Customer $customer, string $traceId): ?array
    {
        $timer = new ExecutionTimer();
        $router = Mikrotik::find($customer->mikrotik_id);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

        if (! $router || ! $autoIsolir) {
            LogHelper::logWarning('ProcessMikrotikCancellation', [
                'customer_id' => $customer->id,
                'mikrotik_id' => $customer->mikrotik_id,
                'router_found' => (bool) $router,
                'auto_isolir_found' => (bool) $autoIsolir,
            ], 404, 'Router or AutoIsolir not found for Mikrotik cancellation.', $traceId, $timer->lap());

            return ['status' => 'failed', 'reason' => 'router_or_autoisolir_not_found'];
        }

        $day = Carbon::now()->format('d');
        $dueDate = $this->getDueDate($autoIsolir, $customer, $traceId);
        $commentUnpayment = $this->getCommentUnpayment($autoIsolir, $customer, $traceId);

        if ($dueDate === null || $commentUnpayment === null) {
            LogHelper::logWarning('ProcessMikrotikCancellation', [
                'customer_id' => $customer->id,
                'due_date' => $dueDate,
                'comment_unpayment' => $commentUnpayment,
            ], 400, 'Skipping Mikrotik cancellation because due date or comment is null.', $traceId, $timer->lap());

            return ['status' => 'failed', 'reason' => 'due_date_or_comment_null'];
        }

        if ($customer->internet_service === 'ppp') {
            $this->cancelPppPayment($router, $customer, $day, $dueDate, $commentUnpayment, $autoIsolir, $traceId);
            return ['type' => 'ppp', 'scheduled' => true];
        }

        if ($customer->internet_service === 'ip_static') {
            $this->cancelIpStaticPayment($router, $customer, $day, $dueDate, $commentUnpayment, $autoIsolir, $traceId);
            return ['type' => 'ip_static', 'scheduled' => true];
        }

        LogHelper::logWarning('ProcessMikrotikCancellation', [
            'customer_id' => $customer->id,
            'internet_service' => $customer->internet_service,
        ], 400, 'Unknown internet_service for Mikrotik cancellation.', $traceId, $timer->lap());

        return ['status' => 'failed', 'reason' => 'unknown_internet_service'];
    }

    public function getDueDate(AutoIsolir $autoIsolir, Customer $customer, string $traceId): ?string
    {
        $timer = new ExecutionTimer();

        try {
            if ($autoIsolir->activation_date === 'false') {
                $dueDate = $autoIsolir->due_date;
            } else {
                $dueDate = Carbon::parse($customer->activation_date)->format('d');
            }

            LogHelper::logInfo('GetDueDate', [
                'customer_id' => $customer->id,
                'activation_date_flag' => $autoIsolir->activation_date,
                'due_date' => $dueDate,
            ], 200, 'Successfully retrieved due date', $traceId, $timer->lap());

            return $dueDate;
        } catch (\Throwable $e) {
            LogHelper::logError('GetDueDate', [
                'customer_id' => $customer->id,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
            ], 500, 'Failed to get due date', $traceId, $timer->lap());

            return null;
        }
    }

    public function getCommentUnpayment(AutoIsolir $autoIsolir, Customer $customer, string $traceId): ?string
    {
        $timer = new ExecutionTimer();

        try {
            if ($autoIsolir->activation_date === 'false') {
                $comment = $autoIsolir->comment_unpayment;
            } else {
                $date = Carbon::parse($customer->activation_date)->format('d');
                $periodeName = BillingPeriode::latest('created_at')->value('name');

                if (! $periodeName) {
                    throw new \RuntimeException('Billing periode name not found.');
                }

                $month = Carbon::parse($periodeName)->format('m');
                $year = Carbon::parse($periodeName)->format('Y');

                $comment = $autoIsolir->comment_unpayment . '_' . $date . '_' . $month . '_' . $year;
            }

            LogHelper::logInfo('GetCommentUnpayment', [
                'customer_id' => $customer->id,
                'comment_unpayment' => $comment
            ], 200, 'Successfully retrieved comment unpayment', $traceId, $timer->lap());

            return $comment;
        } catch (\Throwable $e) {
            LogHelper::logError('GetCommentUnpayment', [
                'customer_id' => $customer->id,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
            ], 500, 'Failed to get comment unpayment', $traceId, $timer->lap());

            return null;
        }
    }

    public function cancelPppPayment(
        Mikrotik $router,
        Customer $customer,
        string $day,
        string $dueDate,
        string $commentUnpayment,
        AutoIsolir $autoIsolir,
        string $traceId
    ): void {
        $timer = new ExecutionTimer();

        try {
            if ($day <= $dueDate) {
                dispatch(new CancelPaymentBeforeDueDateJob(
                    $router,
                    $commentUnpayment,
                    $customer->secret_id,
                    $traceId
                ))->onQueue('default');

                $message = 'Successfully dispatched cancel PPP payment before due date job';
            } else {
                dispatch(new CancelPaymentAfterDueDateJob(
                    $router,
                    $commentUnpayment,
                    $autoIsolir->profile_id,
                    $customer->secret_id,
                    $customer->username,
                    $traceId
                ))->onQueue('default');

                $message = 'Successfully dispatched cancel PPP payment after due date job';
            }

            LogHelper::logInfo('CancelPppPayment', [
                'customer_id' => $customer->id,
                'router_id' => $router->id,
                'day' => $day,
                'due_date' => $dueDate,
            ], 200, $message, $traceId, $timer->lap());
        } catch (\Throwable $e) {
            LogHelper::logError('CancelPppPayment', [
                'customer_id' => $customer->id,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
            ], 500, 'Failed to dispatch PPP cancellation job', $traceId, $timer->lap());
        }
    }

    public function cancelIpStaticPayment(
        Mikrotik $router,
        Customer $customer,
        string $day,
        string $dueDate,
        string $commentUnpayment,
        AutoIsolir $autoIsolir,
        string $traceId
    ): void {
        $timer = new ExecutionTimer();

        try {
            $comment = $customer->username . '_' . $commentUnpayment;

            if ($day <= $dueDate) {
                dispatch(new CancelPaymentIpStaticBeforeDueDateJob(
                    $router,
                    $customer->simpleque_id,
                    $commentUnpayment,
                    $traceId
                ))->onQueue('default');

                $message = 'Successfully dispatched cancel IP static payment before due date job';
            } else {
                dispatch(new CancelPaymentIpStaticAfterDueDateJob(
                    $router,
                    $autoIsolir->address_list_isolir,
                    $customer->ip_static,
                    $customer->simpleque_id,
                    $comment,
                    $traceId
                ))->onQueue('default');

                $message = 'Successfully dispatched cancel IP static payment after due date job';
            }

            LogHelper::logInfo('CancelIpStaticPayment', [
                'customer_id' => $customer->id,
                'router_id' => $router->id,
                'day' => $day,
                'due_date' => $dueDate,
            ], 200, $message, $traceId, $timer->lap());
        } catch (\Throwable $e) {
            LogHelper::logError('CancelIpStaticPayment', [
                'customer_id' => $customer->id,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
            ], 500, 'Failed to dispatch IP Static cancellation job', $traceId, $timer->lap());
        }
    }

    /**
     * Update status billing.
     */
    public function updateBillingStatus(Billing $billing, string $traceId): void
    {
        $timer = new ExecutionTimer();
        $userId = auth()->check() ? auth()->user()->id : null;

        $billing->update([
            'updated_by' => $userId,
            'status' => 'BL',
        ]);

        LogHelper::logInfo('UpdateBillingStatus', [
            'billing_id' => $billing->id,
            'status_before' => $billing->getOriginal('status'),
            'status_after' => 'BL',
            'updated_by' => $userId,
        ], 200, 'Successfully reverted billing status to BL (Belum Lunas).', $traceId, $timer->lap());
    }

    /**
     * Ambil data receipt asli, hard delete receipt, dan hard delete billing payment.
     */
    public function handleReceipt(BillingPayment $payment, string $traceId): ?array
    {
        $timer = new ExecutionTimer();
        $payment->loadMissing('receipt');
        $receipt = $payment->receipt;
        $payment_id = $payment->id;
        $billing_id = $payment->billing_id;

        if (! $receipt) {
            LogHelper::logWarning('HandleReceipt', [
                'payment_id' => $payment_id,
                'billing_id' => $billing_id,
            ], 404, 'No receipt associated with this payment. Skipping delete.', $traceId, $timer->lap());

            return null;
        }

        $data = $receipt->toArray();

        // 1. Hard delete si receipt
        $receipt->forceDelete();

        // 2. Hard delete si billing payment
        $payment->forceDelete();

        LogHelper::logInfo('HandleReceipt', [
            'receipt_id' => $data['id'] ?? null,
            'payment_id' => $payment_id,
            'billing_id' => $billing_id,
        ], 200, 'Successfully executed hard delete on both Receipt and BillingPayment.', $traceId, $timer->lap());

        return $data;
    }

    /**
     * Update order terkait billing menjadi canceled.
     */
    public function updateRelatedOrders(Billing $billing, string $traceId): void
    {
        $timer = new ExecutionTimer();
        $orders = Order::where('billing_id', $billing->id)->get();

        if ($orders->isEmpty()) {
            LogHelper::logWarning('UpdateRelatedOrders', [
                'billing_id' => $billing->id
            ], 404, 'No related orders found for this billing.', $traceId, $timer->lap());

            return;
        }

        foreach ($orders as $order) {
            $order->update([
                'payment_status' => OrderStatus::CANCELED,
            ]);
        }

        LogHelper::logInfo('UpdateRelatedOrders', [
            'billing_id' => $billing->id,
            'order_ids' => $orders->pluck('id')->toArray(),
            'order_count' => $orders->count(),
        ], 200, 'Successfully updated related orders status to CANCELED.', $traceId, $timer->lap());
    }
}
