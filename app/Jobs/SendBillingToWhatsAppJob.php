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
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use App\Models\WhatsAppGateway;
use App\Helpers\LogHelper;

class SendBillingToWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private WhatsappMessageService $messageService;

    public function __construct()
    {
        $this->messageService = app(WhatsappMessageService::class);
    }

    public function handle(): void
    {
        try {
            $gateway = WhatsappGateway::where('disabled', false)->get();

            if ($gateway->isEmpty()) {
                LogHelper::logWarning(
                    null,
                    null,
                    404, // Not Found
                    "No active WhatsApp gateway found."
                );
                return;
            }

            $lastBillingPeriode = BillingPeriode::latest('created_at')->first();

            if (!$lastBillingPeriode) {
                LogHelper::logWarning(
                    null,
                    null,
                    404, // Not Found
                    "No billing period found."
                );
                return;
            }

            $billings = Billing::query()
                ->join('customers', 'billings.customer_id', '=', 'customers.id')
                ->leftJoin('users', 'customers.user_id', '=', 'users.id')
                ->select(
                    'billings.customer_id',
                    'customers.customer_id as customer_unique_id',
                    'customers.name as customer_name',
                    'customers.contact as customer_contact',
                    'customers.address as customer_address',
                    'billings.due_date',
                    'users.name as admin_name',
                    DB::raw('COUNT(billings.id) as total_bills'),
                    DB::raw('SUM(billings.paket_price) as total_amount')
                )
                ->whereNotNull('customers.contact')
                ->where('customers.contact', '!=', '')
                ->where(function ($query) {
                    $query->where('billings.status', 'BL')
                        ->whereNotNull('billings.due_date');
                })
                ->orWhere(function ($query) {
                    $query->where('billings.status', 'PL')
                        ->whereNotNull('billings.pay_later');
                })
                ->groupBy(
                    'billings.customer_id',
                    'customers.customer_id',
                    'customers.name',
                    'customers.contact',
                    'customers.address',
                    'billings.due_date',
                    'users.name'
                )
                ->orderBy('customers.name')
                ->cursor();

            $count = 0;
            foreach ($billings as $billing) {
                $count++;
            }

            if ($count === 0) {
                LogHelper::logWarning(
                    null,
                    ['periode_id' => $lastBillingPeriode->id],
                    404,
                    "No billing data found."
                );
                return;
            }

            LogHelper::logInfo(
                null,
                ['periode_id' => $lastBillingPeriode->id, 'count' => $count],
                200,
                "{$count} billing records found."
            );

            $this->processBillings($billings, 'create_new_bill');
        } catch (RequestException $e) {
            LogHelper::logError(
                null,
                ['billing_id' => null, 'customer_id' => null],
                500,
                "HTTP Request Exception: " . $e->getMessage()
            );
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['billing_id' => null, 'customer_id' => null],
                500,
                "Exception occurred while creating billing: " . $e->getMessage()
            );
        }
    }

    private function processBillings($billings, $messageType)
    {
        $batchSize = 50; // Ukuran batch yang diinginkan
        $batch = [];
    
        foreach ($billings as $billing) {
            // Add billing record to batch
            $batch[] = $billing;
    
            // If batch reaches the desired size, send it
            if (count($batch) >= $batchSize) {
                $this->sendMessagesInBatch($batch, $messageType); // Send full batch
                $batch = []; // Reset batch after sending
            }
        }
    
        // Send any remaining batch if there are left-over items
        if (count($batch) > 0) {
            $this->sendMessagesInBatch($batch, $messageType); // Send remaining batch
        }
    }
    
    private function sendMessagesInBatch($batch, $messageType)
    {
        $batchSize = 5; // Jumlah pesan per batch
    
        // Process the batch in chunks of size $batchSize
        foreach (array_chunk($batch, $batchSize) as $chunk) {
            foreach ($chunk as $billing) {
                // Make sure we're not dealing with an empty or invalid $billing
                if (empty($billing) || !is_object($billing)) {
                    LogHelper::logWarning(
                        null,
                        null,
                        404, // Not Found
                        "Invalid or empty billing record encountered in batch processing."
                    );
                    continue; // Skip to the next iteration
                }
    
                try {
                    // Try sending the message using the message service
                    $this->messageService->bill_info($billing, $messageType);
    
                    // Log successful message dispatch
                    LogHelper::logInfo(
                        null,
                        [
                            'customer_name' => $billing->customer_name,
                            'customer_contact' => $billing->customer_contact,
                            'message_type' => $messageType
                        ],
                        200, // OK
                        "WhatsApp message creation successful and request sent to messaging service"
                    );
                } catch (\Exception $e) {
                    // Log any errors that occur while trying to send the message
                    LogHelper::logError(
                        null,
                        [
                            'customer_name' => $billing->customer_name,
                            'customer_contact' => $billing->customer_contact
                        ],
                        500, // Internal Server Error
                        "Failed to send WhatsApp message to {$billing->customer_name}. Error: {$e->getMessage()}"
                    );
                }
            }
        }
    }
}
