<?php

namespace App\Jobs\Telegram;

use App\DataTransferObjects\TelegramJobDtoJobDto;
use App\Helpers\LogHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TelegramMessageService;
use Throwable;
use App\DataTransferObjects\TelegramJobDto;

class ProcessTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private TelegramJobDto $jobData) {}

    public function viaQueue()
    {
        return 'telegram';
    }

    public function handle()
    {
        $action = $this->jobData->action;
        $traceId = $this->jobData->traceId;
        $customerId = $this->jobData->customerId;

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
