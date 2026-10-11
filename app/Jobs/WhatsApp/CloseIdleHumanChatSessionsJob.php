<?php

namespace App\Jobs\WhatsApp;

use App\Models\WhatsApp\WhatsAppChatSession;
use App\Models\WhatsApp\WhatsAppMessage;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Messaging\Handlers\WebhookReplyHandler;
use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CloseIdleHumanChatSessionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $sessionIds,
        protected string $traceId
    ) {
        // 🎯 Set nama queue custom di sini secara dinamis atau hardcode
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(MessageDispatcher $dispatcher): void
    {
        $timer = new ExecutionTimer();

        // Ambil data session berdasarkan ID yang dikirim oleh Command
        $idleSessions = WhatsAppChatSession::whereIn('id', $this->sessionIds)->get();

        if ($idleSessions->isEmpty()) {
            return;
        }

        $closingKeyword = WhatsAppMessage::where('slug', 'session_timeout')->first();

        foreach ($idleSessions as $session) {
            try {
                $targetDeviceId = $session->device_id;

                // 1. Kembalikan mode ke bot
                $session->update([
                    'chat_mode' => 'bot',
                    'last_human_activity_at' => null
                ]);

                // 2. Kirim pesan otomatis penutupan sesi
                if ($closingKeyword) {
                    $dispatcher->dispatch(
                        action: $closingKeyword->slug,
                        handlerFactory: fn(string $category) => new WebhookReplyHandler(
                            sender: $session->phone_number,
                            keyword: $closingKeyword,
                            customerId: $session->customer_id,
                            category: $category
                        ),
                        traceId: $this->traceId,
                        eventId: null,
                        deviceId: $targetDeviceId
                    );
                }

                LogHelper::logInfo(null, [
                    'phone_number' => $session->phone_number,
                    'device_id'    => $targetDeviceId,
                ], 200, '[Job] Session timeout processed via Queue.', $this->traceId, $timer->lap());
            } catch (\Throwable $e) {
                $exceptionCode = $e->getCode();
                $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                    ? (int) $exceptionCode
                    : 500;

                LogHelper::logError(null, [
                    'phone_number'    => $session->phone_number,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ], $statusCode, '[Job] Failed to execute session timeout from Queue', $this->traceId, $timer->lap());
            }
        }
    }
}
