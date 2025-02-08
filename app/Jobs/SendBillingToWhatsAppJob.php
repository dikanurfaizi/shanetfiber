<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Bank;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsappMessageService;

class SendBillingToWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private WhatsappMessageService $messageService;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->messageService = app(WhatsappMessageService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Message Service start to running.");
            $this->messageService->create_billing();
        } catch (\Exception $e) {
            Log::error("Failed to execute job send WhatsApp message. Error: {$e->getMessage()}");
        }
    }
}
