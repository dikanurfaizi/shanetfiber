<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Models\Billing\Billing;
use App\Models\Customer;
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
        $allowedDates = [10, 15, 20];

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
        $gateways = WhatsappGateway::where('disabled', false)
            ->get();

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
            // Log::info("Processing gateway {$gateway->name} with remaining day: {$gateway->remaining_day}");

            // Query untuk masing-masing kategori tagihan
            $billingsPastDue = Billing::query()
                ->join('customers', 'billings.customer_id', '=', 'customers.id')
                ->select(
                    'billings.customer_id', // Alias untuk customer_id dari billings
                    'customers.customer_id as customer_unique_id', // Alias untuk customer_id dari customers
                    'customers.name as customer_name',
                    'customers.contact as customer_contact',
                    'customers.address as customer_address',
                    DB::raw('COUNT(billings.id) as total_bills'), // Jumlah tagihan
                    DB::raw('SUM(billings.paket_price) as total_amount') // Total harga tagihan
                )
                ->whereNotNull('customers.contact') // Kontak tidak null
                ->where('customers.contact', '!=', '') // Kontak tidak kosong
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
                    'customers.address'
                )
                ->cursor();

            $billingsDueToday = Billing::query()
                ->join('customers', 'billings.customer_id', '=', 'customers.id')
                ->select(
                    'billings.customer_id', // Alias untuk customer_id dari billings
                    'customers.customer_id as customer_unique_id', // Alias untuk customer_id dari customers
                    'customers.name as customer_name',
                    'customers.contact as customer_contact',
                    'customers.address as customer_address',
                    DB::raw('COUNT(billings.id) as total_bills'), // Jumlah tagihan
                    DB::raw('SUM(billings.paket_price) as total_amount') // Total harga tagihan
                )
                ->whereNotNull('customers.contact') // Kontak tidak null
                ->where('customers.contact', '!=', '') // Kontak tidak kosong
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
                    'customers.address'
                )
                ->cursor();

            $billingsUpcoming = Billing::query()
                ->join('customers', 'billings.customer_id', '=', 'customers.id')
                ->select(
                    'billings.customer_id', // Alias untuk customer_id dari billings
                    'customers.customer_id as customer_unique_id', // Alias untuk customer_id dari customers
                    'customers.name as customer_name',
                    'customers.contact as customer_contact',
                    'customers.address as customer_address',
                    DB::raw('COUNT(billings.id) as total_bills'), // Jumlah tagihan
                    DB::raw('SUM(billings.paket_price) as total_amount') // Total harga tagihan
                )
                ->whereNotNull('customers.contact') // Kontak tidak null
                ->where('customers.contact', '!=', '') // Kontak tidak kosong
                ->where(function ($query) use ($today) {
                    $query->where(function ($subQuery) use ($today) {
                        $subQuery->where('billings.status', 'BL') // Status belum lunas
                            ->whereNotNull('billings.due_date') // Pastikan due_date tidak null
                            ->whereDate('billings.due_date', '>', $today); // due_date >= hari ini
                    })
                        ->orWhere(function ($subQuery) use ($today) {
                            $subQuery->where('billings.status', 'PL') // Status pay later
                                ->whereNotNull('billings.pay_later') // Pastikan pay_later tidak null
                                ->whereDate('billings.pay_later', '>', $today); // pay_later >= hari ini
                        });
                })
                ->groupBy(
                    'billings.customer_id',
                    'customers.customer_id',
                    'customers.name',
                    'customers.contact',
                    'customers.address'
                )
                ->cursor();

            //Log::info("Billing Lewat Jatuh Tempo: $billingsPastDue");
            //Log::info("Billing Jatuh Tempo Hari Ini: $billingsDueToday");
            //Log::info("Billing Akan Jatuh Tempo: $billingsUpcoming");

            // Proses masing-masing kondisi
            $this->processBillings($billingsPastDue, 'warning_bill', $gateway);
            $this->processBillings($billingsDueToday, 'deadline_bill', $gateway);
            $this->processBillings($billingsUpcoming, 'reminder_bill', $gateway);
        }
    }

    private function processBillings($billings, $messageType, $gateway)
    {
        $batchSize = 50; // Ukuran batch yang diinginkan
        $batch = [];

        foreach ($billings as $billing) {
            // Log::info("Processing billing for {$billing->customer_name} with Message Type {$messageType}");

            // Cek jika pesan sudah pernah dikirim ke nomor ini untuk jenis tagihan yang sama hari ini
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

            // Jika batch sudah penuh, kirim pesan dalam batch
            if (count($batch) >= $batchSize) {
                $this->sendMessagesInBatch($batch, $messageType, $gateway);
                $batch = []; // Reset batch setelah pengiriman
            }
        }

        // Kirim sisa batch jika ada
        if (count($batch) > 0) {
            $this->sendMessagesInBatch($batch, $messageType, $gateway);
        }
    }

    private function sendMessagesInBatch($batch, $messageType, $gateway)
    {
        $batchSize = 5; // Jumlah pesan per batch
        $successCount = 0;

        $chunks = array_chunk($batch, $batchSize);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $billing) {
                if (!$billing) {
                    LogHelper::logWarning(
                        null,
                        null,
                        404,
                        "No customer billing found."
                    );
                    continue;
                }

                try {
                    // Kirim pesan
                    $this->messageService->bill_info($billing, $messageType);

                    // Simpan log setelah berhasil mengirim
                    MessageLog::create([
                        'customer_id' => $billing->customer_id,
                        'message_type' => $messageType,
                        'gateway_id' => $gateway->id,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    LogHelper::logError(
                        null,
                        [
                            'customer_name' => $billing->customer_name,
                            'customer_contact' => $billing->customer_contact,
                            'message_type' => $messageType,
                        ],
                        500,
                        "Failed to send WhatsApp message: " . $e->getMessage()
                    );
                }
            }
        }

        LogHelper::logInfo(
            null,
            [
                'message_type' => $messageType,
                'gateway_id' => $gateway->id,
                'success_count' => $successCount,
                'total_count' => count($batch),
            ],
            200,
            "Message sending completed. Total sent successfully: {$successCount} of " . count($batch)
        );
    }
}
