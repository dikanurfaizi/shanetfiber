<?php

namespace App\Services\Billings;

use App\Enums\OrderStatus;
use App\Events\PaymentCancelled;
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
        $mikrotikResult = [
            'status' => 'skipped',
            'reason' => 'mikrotik_action_disabled',
        ];
        try {
            DB::transaction(function () use ($payment, $traceId, &$originalReceipt) {

                $payment->lockForUpdate();

                $billing = $payment->billing()->lockForUpdate()->firstOrFail();

                $originalReceipt = $this->handleReceipt($payment);

                $this->updateBillingStatus($billing, $traceId);
                $this->updateRelatedOrders($billing, $traceId);
            });

            if (config('services.shanetfiber.allow_mikrotik_actions')) {
                // 5) Setelah DB commit → operasi ke sistem eksternal (Mikrotik)
                $mikrotikResult = $this->processMikrotikCancellation($payment->billing->customer, $traceId);
            }

            // 6) NOTIFIKASI → LEWAT EVENT
            event(new PaymentCancelled(
                billingId: $payment->billing_id,
                receiptId: $originalReceipt['id'] ?? null,
                receiptData: $originalReceipt,
                traceId: $traceId,
            ));

            return [
                'success' => true,
                'message' => 'Pembatalan pembayaran untuk ' . $payment->billing->customer->name . ' berhasil diproses.',
                'receipt' => $originalReceipt,
                'mikrotik' => $mikrotikResult,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            LogHelper::logError(null, [
                'billing_id' => $payment->billing_id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ], 500, 'Failed to cancel payment.', $traceId);

            return [
                'success' => false,
                'message' => 'Gagal melakukan unpayment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Menyiapkan pembatalan di Mikrotik: hitung due date/comment lalu dispatch job ke queue.
     * Tidak ikut transaksi DB.
     */
    public function processMikrotikCancellation(Customer $customer, string $traceId): ?array
    {
        $router = Mikrotik::find($customer->mikrotik_id);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

        if (! $router || ! $autoIsolir) {
            LogHelper::logWarning('ProcessMikrotikCancellation', [
                'customer_id' => $customer->id,
                'mikrotik_id' => $customer->mikrotik_id,
                'router_found' => (bool) $router,
                'auto_isolir_found' => (bool) $autoIsolir,
            ], 404, 'Router or AutoIsolir not found for Mikrotik cancellation.', $traceId);

            return null;
        }

        $day = Carbon::now()->format('d');
        $dueDate = $this->getDueDate($autoIsolir, $customer, $traceId);
        $commentUnpayment = $this->getCommentUnpayment($autoIsolir, $customer, $traceId);

        if ($dueDate === null || $commentUnpayment === null) {
            LogHelper::logWarning('ProcessMikrotikCancellation', [
                'customer_id' => $customer->id,
                'due_date' => $dueDate,
                'comment_unpayment' => $commentUnpayment,
            ], 400, 'Skipping Mikrotik cancellation because due date or comment is null.', $traceId);

            return null;
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
        ], 400, 'Unknown internet_service for Mikrotik cancellation.', $traceId);

        return null;
    }

    public function getDueDate(AutoIsolir $autoIsolir, Customer $customer, string $traceId): ?string
    {
        $startTime = microtime(true);

        try {
            LogHelper::logInfo('GetDueDate', [
                'trace_id' => $traceId,
                'auto_isolir_id' => $autoIsolir->id ?? null,
                'customer_id' => $customer->id ?? null,
                'activation_date_flag' => $autoIsolir->activation_date,
            ], 200, 'Starting getDueDate process', $traceId);

            if ($autoIsolir->activation_date === 'false') {
                $dueDate = $autoIsolir->due_date;
            } else {
                $dueDate = Carbon::parse($customer->activation_date)->format('d');
            }

            $executionTime = round(microtime(true) - $startTime, 3);

            LogHelper::logInfo('GetDueDate', [
                'trace_id' => $traceId,
                'due_date' => $dueDate,
                'execution_seconds' => $executionTime,
            ], 200, 'Successfully retrieved due date', $traceId);

            return $dueDate;
        } catch (\Throwable $e) {
            LogHelper::logError('GetDueDate', [
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to get due date', $traceId);

            return null;
        }
    }

    public function getCommentUnpayment(AutoIsolir $autoIsolir, Customer $customer, string $traceId): ?string
    {
        $startTime = microtime(true);

        try {
            LogHelper::logInfo('GetCommentUnpayment', [
                'trace_id' => $traceId,
                'auto_isolir_id' => $autoIsolir->id ?? null,
                'customer_id' => $customer->id ?? null,
                'activation_date_flag' => $autoIsolir->activation_date,
            ], 200, 'Starting getCommentUnpayment process', $traceId);

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

            $executionTime = round(microtime(true) - $startTime, 3);

            LogHelper::logInfo('GetCommentUnpayment', [
                'trace_id' => $traceId,
                'comment_unpayment' => $comment,
                'execution_seconds' => $executionTime,
            ], 200, 'Successfully retrieved comment unpayment', $traceId);

            return $comment;
        } catch (\Throwable $e) {
            LogHelper::logError('GetCommentUnpayment', [
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to get comment unpayment', $traceId);

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
        $startTime = microtime(true);

        try {
            LogHelper::logInfo('CancelPppPayment', [
                'trace_id' => $traceId,
                'customer_id' => $customer->id ?? null,
                'router_id' => $router->id ?? null,
                'day' => $day,
                'due_date' => $dueDate,
            ], 200, 'Starting cancel PPP payment process', $traceId);

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

            $executionTime = round(microtime(true) - $startTime, 3);

            LogHelper::logInfo('CancelPppPayment', [
                'trace_id' => $traceId,
                'execution_seconds' => $executionTime,
            ], 200, $message, $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError('CancelPppPayment', [
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to cancel PPP payment', $traceId);
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
        $startTime = microtime(true);

        try {
            LogHelper::logInfo('CancelIpStaticPayment', [
                'trace_id' => $traceId,
                'customer_id' => $customer->id ?? null,
                'router_id' => $router->id ?? null,
                'day' => $day,
                'due_date' => $dueDate,
            ], 200, 'Starting cancel IP Static payment process', $traceId);

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

            $executionTime = round(microtime(true) - $startTime, 3);

            LogHelper::logInfo('CancelIpStaticPayment', [
                'trace_id' => $traceId,
                'execution_seconds' => $executionTime,
            ], 200, $message, $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError('CancelIpStaticPayment', [
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to cancel IP Static payment', $traceId);
        }
    }

    /**
     * Update status billing. Biarkan exception bubble ke cancelPayment().
     */
    public function updateBillingStatus(Billing $billing, string $traceId): void
    {
        $billing->update([
            'updated_by' => auth()->user()->id,
            'status' => 'BL',
        ]);
    }

    /**
     * Ambil data receipt asli, soft delete, dan lepaskan relasi dari billing.
     * Return array data receipt (untuk referensi notifikasi), atau null jika tidak ada.
     */
    public function handleReceipt(BillingPayment $payment): ?array
    {
        $payment->loadMissing('receipt');

        $receipt = $payment->receipt;
        if (! $receipt) {
            return null;
        }

        $data = $receipt->toArray();
        $receipt->delete(); // soft delete

        return $data;
    }

    /**
     * Update order terkait billing menjadi canceled.
     */
    public function updateRelatedOrders(Billing $billing, string $traceId): void
    {
        $orders = Order::where('billing_id', $billing->id)->get();

        if ($orders->isEmpty()) {
            LogHelper::logWarning(
                null,
                ['billing_id' => $billing->id],
                404,
                'No orders found',
                $traceId
            );

            return;
        }

        foreach ($orders as $order) {
            $order->update([
                'payment_status' => OrderStatus::CANCELED,
            ]);
        }

        LogHelper::logInfo(
            null,
            ['order_count' => $orders->count(), 'billing_id' => $billing->id],
            200,
            'Canceled orders',
            $traceId
        );
    }
}
