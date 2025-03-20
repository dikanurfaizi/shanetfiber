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
    // public function handle(): void
    // {
    //     $today = Carbon::today();
    //     $allowedDates = [10, 14, 15, 16, 20];

    //     // Pengecekan apakah hari ini bukan termasuk tanggal yang diperbolehkan
    //     if (!in_array($today->day, $allowedDates)) {
    //         LogHelper::logError(
    //             null,
    //             null,
    //             400, // Bad Request
    //             "Job will not proceed: date not in allowed list. The allowed list is " . json_encode($allowedDates) . " but today is {$today->format('Y-m-d')}"
    //         );
    //         return;
    //     }

    //     Log::info("==================== Start Job Send WhatsApp Message Bill Reminder  ====================");
    //     $gateways = WhatsappGateway::where('disabled', false)
    //         ->get();

    //     if ($gateways->isEmpty()) {
    //         LogHelper::logWarning(
    //             null,
    //             null,
    //             204, // Bad Request
    //             "No active gateways found"
    //         );
    //         return;
    //     }

    //     foreach ($gateways as $gateway) {
    //         // Log::info("Processing gateway {$gateway->name} with remaining day: {$gateway->remaining_day}");

    //         // Query untuk masing-masing kategori tagihan
    //         $billingsPastDue = Billing::query()
    //             ->join('customers', 'billings.customer_id', '=', 'customers.id')
    //             ->select(
    //                 'billings.customer_id', // Alias untuk customer_id dari billings
    //                 'customers.customer_id as customer_unique_id', // Alias untuk customer_id dari customers
    //                 'customers.name as customer_name',
    //                 'customers.contact as customer_contact',
    //                 'customers.address as customer_address',
    //                 DB::raw('COUNT(billings.id) as total_bills'), // Jumlah tagihan
    //                 DB::raw('SUM(billings.paket_price) as total_amount') // Total harga tagihan
    //             )
    //             ->whereNotNull('customers.contact') // Kontak tidak null
    //             ->where('customers.contact', '!=', '') // Kontak tidak kosong
    //             ->where(function ($query) use ($today) {
    //                 $query->where('billings.status', 'BL')
    //                     ->whereNotNull('billings.due_date')
    //                     ->whereDate('billings.due_date', '<', $today)
    //                     ->orWhere(function ($q) use ($today) {
    //                         $q->where('billings.status', 'PL')
    //                             ->whereNotNull('billings.pay_later')
    //                             ->whereDate('billings.pay_later', '<', $today);
    //                     });
    //             })
    //             ->groupBy(
    //                 'billings.customer_id',
    //                 'customers.customer_id',
    //                 'customers.name',
    //                 'customers.contact',
    //                 'customers.address'
    //             )
    //             ->cursor();

    //         $billingsDueToday = Billing::query()
    //             ->join('customers', 'billings.customer_id', '=', 'customers.id')
    //             ->select(
    //                 'billings.customer_id', // Alias untuk customer_id dari billings
    //                 'customers.customer_id as customer_unique_id', // Alias untuk customer_id dari customers
    //                 'customers.name as customer_name',
    //                 'customers.contact as customer_contact',
    //                 'customers.address as customer_address',
    //                 DB::raw('COUNT(billings.id) as total_bills'), // Jumlah tagihan
    //                 DB::raw('SUM(billings.paket_price) as total_amount') // Total harga tagihan
    //             )
    //             ->whereNotNull('customers.contact') // Kontak tidak null
    //             ->where('customers.contact', '!=', '') // Kontak tidak kosong
    //             ->where(function ($query) use ($today) {
    //                 $query->where('billings.status', 'BL')
    //                     ->whereNotNull('billings.due_date')
    //                     ->whereDate('billings.due_date', $today)
    //                     ->orWhere(function ($q) use ($today) {
    //                         $q->where('billings.status', 'PL')
    //                             ->whereNotNull('billings.pay_later')
    //                             ->whereDate('billings.pay_later', $today);
    //                     });
    //             })
    //             ->groupBy(
    //                 'billings.customer_id',
    //                 'customers.customer_id',
    //                 'customers.name',
    //                 'customers.contact',
    //                 'customers.address'
    //             )
    //             ->cursor();

    //         $billingsUpcoming = Billing::query()
    //             ->join('customers', 'billings.customer_id', '=', 'customers.id')
    //             ->select(
    //                 'billings.customer_id', // Alias untuk customer_id dari billings
    //                 'customers.customer_id as customer_unique_id', // Alias untuk customer_id dari customers
    //                 'customers.name as customer_name',
    //                 'customers.contact as customer_contact',
    //                 'customers.address as customer_address',
    //                 DB::raw('COUNT(billings.id) as total_bills'), // Jumlah tagihan
    //                 DB::raw('SUM(billings.paket_price) as total_amount') // Total harga tagihan
    //             )
    //             ->whereNotNull('customers.contact') // Kontak tidak null
    //             ->where('customers.contact', '!=', '') // Kontak tidak kosong
    //             ->where(function ($query) use ($today) {
    //                 $query->where(function ($subQuery) use ($today) {
    //                     $subQuery->where('billings.status', 'BL') // Status belum lunas
    //                         ->whereNotNull('billings.due_date') // Pastikan due_date tidak null
    //                         ->whereDate('billings.due_date', '>', $today); // due_date >= hari ini
    //                 })
    //                     ->orWhere(function ($subQuery) use ($today) {
    //                         $subQuery->where('billings.status', 'PL') // Status pay later
    //                             ->whereNotNull('billings.pay_later') // Pastikan pay_later tidak null
    //                             ->whereDate('billings.pay_later', '>', $today); // pay_later >= hari ini
    //                     });
    //             })
    //             ->groupBy(
    //                 'billings.customer_id',
    //                 'customers.customer_id',
    //                 'customers.name',
    //                 'customers.contact',
    //                 'customers.address'
    //             )
    //             ->cursor();

    //         //Log::info("Billing Lewat Jatuh Tempo: $billingsPastDue");
    //         //Log::info("Billing Jatuh Tempo Hari Ini: $billingsDueToday");
    //         //Log::info("Billing Akan Jatuh Tempo: $billingsUpcoming");

    //         $logPastDueDateData = $billingsPastDue->map(function ($billing) {
    //             return [
    //                 'customer_name' => $billing->customer_name,
    //                 'message_type' => 'warning_bill',
    //             ];
    //         })->toArray();

    //         $logDeadlineData = $billingsDueToday->map(function ($billing) {
    //             return [
    //                 'customer_name' => $billing->customer_name,
    //                 'message_type' => 'deadline_bill',
    //             ];
    //         })->toArray();

    //         $logUpcomingData = $billingsUpcoming->map(function ($billing) {
    //             return [
    //                 'customer_name' => $billing->customer_name,
    //                 'message_type' => 'reminder_bill',
    //             ];
    //         })->toArray();

    //         if (!empty($logPastDueDateData)) {
    //             LogHelper::logInfo(
    //                 null,
    //                 $logPastDueDateData,
    //                 200, // Bad Request
    //                 "List of names of customers who have not paid and are past due"
    //             );
    //         }

    //         if (!empty($logDeadlineData)) {
    //             LogHelper::logInfo(
    //                 null,
    //                 $logDeadlineData,
    //                 200, // Bad Request
    //                 "List of customer names who have not paid and are due today"
    //             );
    //         }

    //         if (!empty($logUpcomingData)) {
    //             LogHelper::logInfo(
    //                 null,
    //                 $logUpcomingData,
    //                 200, // Bad Request
    //                 "List of names of customers who have not paid and will be due in a few days"
    //             );
    //         }

    //         // Proses masing-masing kondisi
    //         $this->processBillings($billingsPastDue, 'warning_bill', $gateway);
    //         $this->processBillings($billingsDueToday, 'deadline_bill', $gateway);
    //         $this->processBillings($billingsUpcoming, 'reminder_bill', $gateway);
    //     }
    // }

    public function handle(): void
    {
        $today = Carbon::today();
        $allowedDates = [10, 14, 15, 16, 20];

        if (!in_array($today->day, $allowedDates)) {
            LogHelper::logError(
                null,
                null,
                400,
                "Job will not proceed: date not in allowed list. Allowed dates: " . json_encode($allowedDates) . " but today is {$today->format('Y-m-d')}"
            );
            return;
        }

        Log::info("==================== Start Job Send WhatsApp Message Bill Reminder ====================");
        $gateways = WhatsappGateway::where('disabled', false)->get();

        if ($gateways->isEmpty()) {
            LogHelper::logWarning(null, null, 204, "No active gateways found");
            return;
        }

        $billingCategories = [
            'warning_bill' => [
                'condition' => fn($query, $date) => $query->where(function ($q) use ($date) {
                    $q->where('billings.status', 'BL')->whereNotNull('billings.due_date')->whereDate('billings.due_date', '<', $date)
                        ->orWhere(function ($q2) use ($date) {
                            $q2->where('billings.status', 'PL')->whereNotNull('billings.pay_later')->whereDate('billings.pay_later', '<', $date);
                        });
                }),
                'message' => "Customers who have not paid and are past due"
            ],
            'deadline_bill' => [
                'condition' => fn($query, $date) => $query->where(function ($q) use ($date) {
                    $q->where('billings.status', 'BL')->whereNotNull('billings.due_date')->whereDate('billings.due_date', $date)
                        ->orWhere(function ($q2) use ($date) {
                            $q2->where('billings.status', 'PL')->whereNotNull('billings.pay_later')->whereDate('billings.pay_later', $date);
                        });
                }),
                'message' => "Customers who have not paid and are due today"
            ],
            'reminder_bill' => [
                'condition' => fn($query, $date) => $query->where(function ($q) use ($date) {
                    $q->where('billings.status', 'BL')->whereNotNull('billings.due_date')->whereDate('billings.due_date', '>', $date)
                        ->orWhere(function ($q2) use ($date) {
                            $q2->where('billings.status', 'PL')->whereNotNull('billings.pay_later')->whereDate('billings.pay_later', '>', $date);
                        });
                }),
                'message' => "Customers who have not paid and will be due soon"
            ],
        ];

        foreach ($gateways as $gateway) {
            foreach ($billingCategories as $messageType => $data) {
                $billings = $this->getBillings($data['condition'], $today);

                if ($billings->isNotEmpty()) {
                    LogHelper::logInfo(null, $billings->map(fn($b) => ['customer_name' => $b->customer_name, 'message_type' => $messageType])->toArray(), 200, $data['message']);
                }

                $this->processBillings($billings, $messageType, $gateway);
            }
        }
    }

    private function getBillings($conditionCallback, $date)
    {
        return Billing::query()
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->select(
                'billings.customer_id',
                'customers.customer_id as customer_unique_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.address as customer_address',
                DB::raw('COUNT(billings.id) as total_bills'),
                DB::raw('SUM(billings.paket_price) as total_amount')
            )
            ->whereNotNull('customers.contact')
            ->where('customers.contact', '!=', '')
            ->where(fn($query) => $conditionCallback($query, $date))
            ->groupBy('billings.customer_id', 'customers.customer_id', 'customers.name', 'customers.contact', 'customers.address')
            ->cursor();
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
                    208, // Bad Request
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
        $pauseDuration = 60; // Jeda antar batch dalam detik (1 menit)

        $chunks = array_chunk($batch, $batchSize);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $billing) {
                if (!$billing) {
                    LogHelper::logWarning(
                        null,
                        null,
                        404, // Not Found
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

                    LogHelper::logInfo(
                        null,
                        [
                            'customer_name' => $billing->customer_name,
                            'customer_contact' => $billing->customer_contact,
                            'message_type' => $messageType,
                        ],
                        200, // OK
                        "Successfully sent WhatsApp message."
                    );
                } catch (\Exception $e) {
                    // Log kegagalan jika ada error di dalam proses pengiriman
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

            // Tunggu sebelum mengirim batch berikutnya
            sleep($pauseDuration);
        }
    }
}
