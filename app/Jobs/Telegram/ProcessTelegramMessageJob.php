<?php

namespace App\Jobs\Telegram;

use App\Helpers\LogHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TelegramMessageService;
use Throwable;

class ProcessTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $customerId;
    protected string $action;
    protected string $traceId;
    protected ?int $billingId;
    protected ?int $receiptId;
    protected ?array $receiptData = null;

    public function __construct(int $customerId, string $action, string $traceId, ?int $billingId = null, ?int $receiptId = null, $receiptData = null)
    {
        $this->customerId = $customerId;
        $this->action = $action;
        $this->traceId = $traceId;
        $this->billingId = $billingId;
        $this->receiptId = $receiptId;
        $this->receiptData = $receiptData;
    }

    public function handle()
    {
        $action = $this->action;
        $traceId = $this->traceId;
        $customerId = $this->customerId;

        try {
            $telegramService = new TelegramMessageService();

            if ($action === 'activation_customer') {
                $telegramService->activation($customerId, $action, $traceId);
                LogHelper::logInfo(
                    null,
                    [
                        'message' => 'Telegram activation message sent',
                        'customer_id' => $customerId
                    ],
                    200,
                    'Telegram Activation',
                    $traceId
                );
            }
        } catch (Throwable $e) {
            LogHelper::logError(
                $e,
                [
                    'message' => 'ProcessTelegramMessageJob ERROR',
                    'customer_id' => $customerId,
                    'action' => $action
                ],
                500,
                'Job Execution Error',
                $traceId
            );
        }
    }
}
