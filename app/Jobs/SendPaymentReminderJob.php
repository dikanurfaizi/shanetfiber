<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Models\Billing\Billing;
use App\Models\MessageLog;
use App\Services\WhatsappMessageService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;

class SendPaymentReminderJob implements ShouldQueue
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
        $today = Carbon::today();
        $allowedDates = [4, 10, 15, 20];

        // Pengecekan apakah hari ini bukan termasuk tanggal yang diperbolehkan
        if (!in_array($today->day, $allowedDates)) {
            LogHelper::logWarning(
                null,
                null,
                422, // Unprocessable Entity
                "Job will not proceed: date not in allowed list. The allowed list is " . json_encode($allowedDates) . " but today is {$today->format('Y-m-d')}"
            );
            return;
        }

        Log::info("==================== Start Job Send WhatsApp Message Bill Reminder  ====================");

        // Mengambil gateways aktif
        $gateways = WhatsappGateway::where('disabled', false)->get();

        if ($gateways->isEmpty()) {
            LogHelper::logWarning(
                null,
                null,
                404, // Not Found
                "No active gateways found"
            );
            return;
        }

        foreach ($gateways as $gateway) {
            Log::info("Processing gateway: {$gateway->name}, remaining days: {$gateway->remaining_day}");

            // Query untuk masing-masing kategori tagihan
            $billingsPastDue = $this->getBillings($today, 'past_due');
            $billingsDueToday = $this->getBillings($today, 'due_today');
            $billingsUpcoming = $this->getBillings($today, 'upcoming');

            Log::info("Processing Billings Past Due: " . count($billingsPastDue) . " records");
            Log::info("Processing Billings Due Today: " . count($billingsDueToday) . " records");
            Log::info("Processing Billings Upcoming: " . count($billingsUpcoming) . " records");

            // Proses masing-masing kondisi
            $this->processBillings($billingsPastDue, 'warning_bill', $gateway);
            $this->processBillings($billingsDueToday, 'deadline_bill', $gateway);
            $this->processBillings($billingsUpcoming, 'reminder_bill', $gateway);
        }
    }

    private function getBillings($today, $type)
    {
        switch ($type) {
            case 'past_due':
                return Billing::query()
                    ->join('customers', 'billings.customer_id', '=', 'customers.id')
                    ->leftJoin('users', 'customers.user_id', '=', 'users.id')  // LEFT JOIN ke tabel users
                    ->select(
                        'billings.customer_id',
                        'billings.billing_number as billing_number',
                        'customers.customer_id as customer_unique_id',
                        'customers.name as customer_name',
                        'customers.contact as customer_contact',
                        'customers.address as customer_address',
                        'users.id as admin_id',
                        'users.name as admin_name',
                        'users.contact as admin_contact'
                    )
                    ->whereNotNull('customers.contact')
                    ->where('customers.contact', '!=', '')
                    ->where(function ($query) use ($today) {
                        $query->where('billings.status', 'BL')
                            ->whereNotNull('billings.due_date')
                            ->whereDate('billings.due_date', '<', $today)
                            ->orWhere(function ($q) use ($today) {
                                $q->where('billings.status', 'PL')
                                    ->whereNotNull('billings.pay_later')
                                    ->whereDate('billings.pay_later', '<', $today);
                            });
                    })
                    ->groupBy(
                        'billings.customer_id',
                        'customers.customer_id',
                        'customers.name',
                        'customers.contact',
                        'customers.address',
                        'users.id',
                        'users.name',
                        'users.contact'
                    )
                    ->cursor();

            case 'due_today':
                return Billing::query()
                    ->join('customers', 'billings.customer_id', '=', 'customers.id')
                    ->leftJoin('users', 'customers.user_id', '=', 'users.id')  // LEFT JOIN ke tabel users
                    ->select(
                        'billings.customer_id',
                        'billings.billing_number as billing_number',
                        'customers.customer_id as customer_unique_id',
                        'customers.name as customer_name',
                        'customers.contact as customer_contact',
                        'customers.address as customer_address',
                        'users.id as admin_id',
                        'users.name as admin_name',
                        'users.contact as admin_contact'
                    )
                    ->whereNotNull('customers.contact')
                    ->where('customers.contact', '!=', '')
                    ->where(function ($query) use ($today) {
                        $query->where('billings.status', 'BL')
                            ->whereNotNull('billings.due_date')
                            ->whereDate('billings.due_date', $today)
                            ->orWhere(function ($q) use ($today) {
                                $q->where('billings.status', 'PL')
                                    ->whereNotNull('billings.pay_later')
                                    ->whereDate('billings.pay_later', $today);
                            });
                    })
                    ->groupBy(
                        'billings.customer_id',
                        'customers.customer_id',
                        'customers.name',
                        'customers.contact',
                        'customers.address',
                        'users.id',
                        'users.name',
                        'users.contact'
                    )
                    ->cursor();

            case 'upcoming':
                return Billing::query()
                    ->join('customers', 'billings.customer_id', '=', 'customers.id')
                    ->leftJoin('users', 'customers.user_id', '=', 'users.id')  // LEFT JOIN ke tabel users
                    ->select(
                        'billings.customer_id',
                        'billings.billing_number as billing_number',
                        'customers.customer_id as customer_unique_id',
                        'customers.name as customer_name',
                        'customers.contact as customer_contact',
                        'customers.address as customer_address',
                        'users.id as admin_id',
                        'users.name as admin_name',
                        'users.contact as admin_contact'
                    )
                    ->whereNotNull('customers.contact')
                    ->where('customers.contact', '!=', '')
                    ->where(function ($query) use ($today) {
                        $query->where('billings.status', 'BL')
                            ->whereNotNull('billings.due_date')
                            ->whereDate('billings.due_date', '>', $today)
                            ->orWhere(function ($q) use ($today) {
                                $q->where('billings.status', 'PL')
                                    ->whereNotNull('billings.pay_later')
                                    ->whereDate('billings.pay_later', '>', $today);
                            });
                    })
                    ->groupBy(
                        'billings.customer_id',
                        'customers.customer_id',
                        'customers.name',
                        'customers.contact',
                        'customers.address',
                        'users.id',
                        'users.name',
                        'users.contact'
                    )
                    ->cursor();

            default:
                return collect(); // Return empty collection if type is not valid
        }
    }

    private function processBillings($billings, $messageType, $gateway)
    {
        $batchSize = 50;
        $batch = [];
        $totalProcessed = 0;
        $successfulMessages = 0;
        $failedMessages = 0;

        foreach ($billings as $billing) {
            // Log processing information
            Log::info("Processing billing for {$billing->customer_name} with Message Type {$messageType}");

            // Cek jika pesan sudah pernah dikirim
            $alreadySent = MessageLog::where('customer_id', $billing->customer_id)
                ->where('message_type', $messageType)
                ->whereDate('created_at', Carbon::today())
                ->exists();

            if ($alreadySent) {
                LogHelper::logWarning(
                    null,
                    [
                        'customer_name' => $billing->customer_name,
                        'message_type' => $messageType,
                    ],
                    409, // Conflict
                    "Skipping redundant message for {$billing->customer_name} with type {$messageType}."
                );
                continue;
            }

            $batch[] = $billing;
            $totalProcessed++;

            // Kirim pesan jika batch sudah cukup
            if (count($batch) >= $batchSize) {
                list($sent, $failed) = $this->sendMessagesInBatch($batch, $messageType, $gateway);
                $successfulMessages += $sent;
                $failedMessages += $failed;
                $batch = []; // Reset batch setelah pengiriman
            }
        }

        // Kirim sisa batch jika ada
        if (count($batch) > 0) {
            list($sent, $failed) = $this->sendMessagesInBatch($batch, $messageType, $gateway);
            $successfulMessages += $sent;
            $failedMessages += $failed;
        }

        // Log summary
        LogHelper::logInfo(
            null,
            [
                'message_type' => $messageType,
                'gateway_id' => $gateway->id,
                'total_processed' => $totalProcessed,
                'successful_messages' => $successfulMessages,
                'failed_messages' => $failedMessages,
            ],
            200,
            "Batch processed for {$messageType}. Total processed: {$totalProcessed}, Successful: {$successfulMessages}, Failed: {$failedMessages}"
        );
    }

    private function sendMessagesInBatch($batch, $messageType, $gateway)
    {
        $successCount = 0;
        $failureCount = 0;

        foreach ($batch as $billing) {
            $message = $this->messageService->bill_info($billing, $messageType);

            if ($message) {
                try {
                    // Kirim pesan ke job, bisa ditambahkan delay di sini
                    SendWhatsappSingleJob::dispatch(
                        $billing->customer_name,
                        $billing->customer_contact,
                        $gateway,
                        $message
                    )->delay(now()->addSeconds(5)); // Delay untuk batch processing

                    MessageLog::create([
                        'customer_id'   => $billing->customer_id,
                        'message_type'  => $messageType,
                        'gateway_id'    => $gateway->id,
                        'content'       => $message,
                        'sent_at'       => now(),
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $failureCount++;
                    LogHelper::logError(null, [
                        'billing_id' => $billing->id,
                        'error' => $e->getMessage(),
                    ], 500, "Failed to dispatch job.");
                }
            } else {
                $failureCount++;
            }
        }

        return [$successCount, $failureCount];
    }
}
