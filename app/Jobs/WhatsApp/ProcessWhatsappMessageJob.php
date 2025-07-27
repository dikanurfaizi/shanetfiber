<?php

namespace App\Jobs\WhatsApp;

use App\Models\Billing\Billing;
use App\Models\Receipt;
use App\Helpers\LogHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Services\WhatsappMessageService;

class ProcessWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $customerId = null;
    protected ?string $action = null;
    protected ?string $traceId = null;
    protected ?int $billingId = null;
    protected ?int $receiptId = null;
    protected ?array $receiptData = null;
    protected ?Carbon $startDate = null;
    protected ?Carbon $endDate = null;
    protected ?int $orderId = null;

    public function __construct(
        ?int $customerId = null,
        ?string $action = null,
        ?string $traceId = null,
        ?int $billingId = null,
        ?int $receiptId = null,
        $receiptData = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $orderId = null
    ) {
        $this->customerId = $customerId;
        $this->action = $action;
        $this->traceId = $traceId;
        $this->billingId = $billingId;
        $this->receiptId = $receiptId;
        $this->receiptData = $receiptData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->orderId = $orderId;
    }

    public function handle()
    {
        $action = $this->action;
        $traceId = $this->traceId;
        $billingId = $this->billingId;
        $receiptId = $this->receiptId;
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $orderId = $this->orderId;

        $whatsappService = new WhatsappMessageService();

        if ($action === 'activation_customer') {
            $whatsappService->activation($this->customerId, $action, $traceId);
            LogHelper::logInfo(null, ['customer_id' => $this->customerId, 'action' => $action], 200, "The activation message has entered the delivery queue", $traceId);
        } elseif (in_array($action, ['transfer', 'cash', 'payment_gateway', 'paylater'])) {
            if ($billingId && $receiptId) {
                $billing = Billing::find($billingId);
                $receipt = Receipt::find($receiptId);

                if ($billing && $receipt) {
                    $whatsappService->payment_paket($billing, $receipt, $action, $traceId);
                    LogHelper::logInfo(null, [
                        'billing_id' => $billing->id,
                        'receipt_id' => $receipt->id,
                        'customer_id' => $billing->customer_id,
                        'amount' => $receipt->total_amount ?? 0,
                    ], 200, "Payment message has been entered into the sending queue", $traceId);
                } else {
                    LogHelper::logWarning(
                        null,
                        [
                            'billing_id' => $this->billingId,
                            'receipt_id' => $this->receiptId,
                            'action' => $action
                        ],
                        404,
                        "Billing or Receipt data not found",
                        $traceId
                    );
                }
            } else {
                LogHelper::logWarning(
                    null,
                    [
                        'billing_id' => $this->billingId,
                        'receipt_id' => $this->receiptId,
                        'action' => $action
                    ],
                    400,
                    "Billing ID or Receipt ID is empty when trying to send a WhatsApp message",
                    $traceId
                );
            }
        } elseif ($action === 'unpayment') {
            $billing = Billing::find($this->billingId);
            if (isset($this->receiptData['id']) && $this->receiptData['id'] !== null) {
                $receipt = new Receipt($this->receiptData);
            } elseif ($this->receiptId) {
                $receipt = Receipt::withTrashed()->find($this->receiptId);
            } else {
                $receipt = null;
            }

            if ($billing && $receipt) {
                $whatsappService->payment_paket($billing, $receipt, $action, $traceId);
                LogHelper::logInfo(null, [
                    'billing_id' => $billing->id,
                    'receipt_id' => $receipt->id ?? null,
                    'customer_id' => $billing->customer_id,
                    'amount' => $receipt->total_amount ?? 0,
                ], 200, "Unpayment message successfully entered into the send queue", $traceId);
            } else {
                LogHelper::logWarning(
                    null,
                    [
                        'billing_id' => $this->billingId,
                        'receipt_id' => $this->receiptId,
                        'action' => $action
                    ],
                    404,
                    "Billing or Receipt data not found",
                    $traceId
                );
            }
        } elseif ($action === 'create_new_bill') {
            $whatsappService->isBillingOpenPeriod($startDate, $endDate, $action, $traceId);
            LogHelper::logInfo(null, ['action' => $action], 200, "The whatsApp search and delivery process has been received", $traceId);
        } elseif ($action === 'send_info_billing') {
            $whatsappService->sendPaymentReminders($traceId);
            LogHelper::logInfo(null, [], 200, 'Send payment reminder successfully proceed and delivery', $traceId);
        } elseif ($action === 'create_order') {
            $whatsappService->createOrder($billingId, $orderId, $action, $traceId);
            LogHelper::logInfo(null, [], 200, 'Send payment reminder successfully proceed and delivery', $traceId);
        }
    }
}
