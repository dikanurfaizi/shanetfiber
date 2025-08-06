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

            // 2. Update status billing
            $this->updateBillingStatus($billing);

            // 3. Hapus receipt jika ada
            $originalReceipt = $this->handleReceipt($billing);

            // 4. Update order terkait
            $this->updateRelatedOrders($billing, $traceId);

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
        $dueDate = $this->getDueDate($autoIsolir, $customer);
        $commentUnpayment = $this->getCommentUnpayment($autoIsolir, $customer);

        if ($customer->internet_service == 'ppp') {
            $this->cancelPppPayment($router, $customer, $day, $dueDate, $commentUnpayment, $autoIsolir);
        } elseif ($customer->internet_service == 'ip_static') {
            $this->cancelIpStaticPayment($router, $customer, $day, $dueDate, $commentUnpayment, $autoIsolir);
        }
    }

    private function getDueDate(AutoIsolir $autoIsolir, Customer $customer): string
    {
        if ($autoIsolir->activation_date == 'false') {
            return $autoIsolir->due_date;
        }

        return Carbon::parse($customer->activation_date)->format('d');
    }

    private function getCommentUnpayment(AutoIsolir $autoIsolir, Customer $customer): string
    {
        if ($autoIsolir->activation_date == 'false') {
            return $autoIsolir->comment_unpayment;
        }

        $date = Carbon::parse($customer->activation_date)->format('d');
        $periodeName = BillingPeriode::latest('created_at')->value('name');
        $month = Carbon::parse($periodeName)->format('m');
        $year = Carbon::parse($periodeName)->format('Y');

        return $autoIsolir->comment_unpayment . '_' . $date . '_' . $month . '_' . $year;
    }

    private function cancelPppPayment(
        Mikrotik $router,
        Customer $customer,
        string $day,
        string $dueDate,
        string $commentUnpayment,
        AutoIsolir $autoIsolir
    ) {
        if ($day <= $dueDate) {
            dispatch(new CancelPaymentBeforeDueDateJob(
                $router,
                $commentUnpayment,
                $customer->secret_id
            ))->onQueue('default');
        } else {
            dispatch(new CancelPaymentAfterDueDateJob(
                $router,
                $commentUnpayment,
                $autoIsolir->profile_id,
                $customer->secret_id,
                $customer->username
            ))->onQueue('default');
        }
    }

    private function cancelIpStaticPayment(
        Mikrotik $router,
        Customer $customer,
        string $day,
        string $dueDate,
        string $commentUnpayment,
        AutoIsolir $autoIsolir
    ) {
        $comment = $customer->username . '_' . $commentUnpayment;

        if ($day <= $dueDate) {
            dispatch(new CancelPaymentIpStaticBeforeDueDateJob(
                $router,
                $customer->simpleque_id,
                $commentUnpayment
            ))->onQueue('default');
        } else {
            dispatch(new CancelPaymentIpStaticAfterDueDateJob(
                $router,
                $autoIsolir->address_list_isolir,
                $customer->ip_static,
                $customer->simpleque_id,
                $comment
            ))->onQueue('default');
        }
    }

    private function updateBillingStatus(Billing $billing)
    {
        $billing->update([
            'teller_name' => auth()->user()->name,
            'status' => 'BL',
            'receipt_id' => null,
        ]);
    }

    private function handleReceipt(Billing $billing): ?Receipt
    {
        $receipt = Receipt::where('id', $billing->receipt_id)->first();
        $originalReceipt = $receipt?->replicate();

        if ($receipt) {
            $receipt->delete();
        }

        return $originalReceipt;
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

    private function sendCancellationNotification(
        Billing $billing,
        ?Receipt $originalReceipt,
        string $traceId
    ) {
        $jobData = new WhatsappJobDto(
            customerId: (int)$billing->customer_id,
            action: WhatsAppAction::UNPAYMENT->value,
            traceId: (string)$traceId,
            billingId: (int)$billing->id,
            receiptId: null,
            receiptData: $originalReceipt,
            startDate: null,
            endDate: null,
            orderId: null
        );

        ProcessWhatsappMessageJob::dispatch($jobData)->onQueue('default');
    }
}
