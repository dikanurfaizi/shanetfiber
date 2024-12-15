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
        Log::info("==================== Start Job Send WhatsApp Message Bill Reminder  ====================");
        $today = Carbon::today();

        $gateways = WhatsappGateway::where('remaining_day', '>', 0)
            ->where('disabled', false)
            ->get();

        if ($gateways->isEmpty()) {
            Log::info("No active gateways found.");
            return;
        }

        foreach ($gateways as $gateway) {
            // Log::info("Processing gateway {$gateway->name} with remaining day: {$gateway->remaining_day}");
            $remainingDays = $gateway->remaining_day;

            // Query untuk masing-masing kategori tagihan
            $billingsPastDue = Billing::query()
                ->join('customers', 'billings.customer_id', '=', 'customers.id')
                ->select(
                    'billings.customer_id',
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
                ->groupBy('customers.address', 'billings.customer_id', 'customers.name', 'customers.contact')
                ->cursor();

            $billingsDueToday = Billing::query()
                ->join('customers', 'billings.customer_id', '=', 'customers.id')
                ->select(
                    'billings.customer_id',
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
                ->groupBy('customers.address', 'billings.customer_id', 'customers.name', 'customers.contact')
                ->cursor();

            $billingsUpcoming = Billing::query()
                ->join('customers', 'billings.customer_id', '=', 'customers.id')
                ->select(
                    'billings.customer_id',
                    'customers.name as customer_name',
                    'customers.contact as customer_contact',
                    'customers.address as customer_address',
                    DB::raw('COUNT(billings.id) as total_bills'), // Jumlah tagihan
                    DB::raw('SUM(billings.paket_price) as total_amount') // Total harga tagihan
                )
                ->whereNotNull('customers.contact') // Kontak tidak null
                ->where('customers.contact', '!=', '') // Kontak tidak kosong
                ->where(function ($query) use ($today, $remainingDays) {
                    $query->where(function ($q1) use ($today, $remainingDays) {
                        // Untuk tagihan BL yang due_date-nya >= hari ini dan tidak lebih dari remainingDays
                        $q1->where('billings.status', 'BL')
                            ->whereNotNull('billings.due_date')
                            ->whereDate('billings.due_date', '>=', $today) // Due date >= hari ini
                            ->whereRaw('DATEDIFF(billings.due_date, ?) <= ?', [$today, $remainingDays]); // Selisih hari <= remainingDays
                    })->orWhere(function ($q2) use ($today, $remainingDays) {
                        // Untuk tagihan PL yang pay_later-nya >= hari ini dan tidak lebih dari remainingDays
                        $q2->where('billings.status', 'PL')
                            ->whereNotNull('billings.pay_later')
                            ->whereDate('billings.pay_later', '>=', $today) // Pay later >= hari ini
                            ->whereRaw('DATEDIFF(billings.pay_later, ?) <= ?', [$today, $remainingDays]); // Selisih hari <= remainingDays
                    });
                })
                ->groupBy(
                    'billings.customer_id',
                    'customers.name',
                    'customers.contact',
                    'customers.address'
                )
                ->orderBy('customers.name')
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
                Log::info("Skipping redundant message for : {$billing->customer_name} with type {$messageType}.");
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
        foreach ($batch as $billing) {
            //Log::info("Billing in SendMessagesInBatch: $billing");

            if ($billing) {
                try {
                    Log::info("Trying to send WhatsApp message to {$billing->customer_name} (Contact: {$billing->customer_contact}) with type {$messageType}");

                    // Kirim pesan
                    $this->messageService->bill_info($billing, $messageType);

                    // Simpan log sebelum mengirim
                    MessageLog::create([
                        'customer_id' => $billing->customer_id,
                        'message_type' => $messageType,
                        'gateway_id' => $gateway->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to send WhatsApp message to {$billing->customer_name}. Error: {$e->getMessage()}");
                }
            } else {
                Log::warning("No customer billing found for customer name: {$billing->customer_name}");
            }
        }
    }
}
