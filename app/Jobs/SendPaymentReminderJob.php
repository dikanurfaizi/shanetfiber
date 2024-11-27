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
        Log::info("Starting Reminder Job");
        $today = Carbon::today();

        $gateways = WhatsappGateway::where('remaining_day', '>', 0)
            ->where('disabled', false)
            ->get();

        if ($gateways->isEmpty()) {
            Log::info("No active gateways found.");
            return;
        }

        foreach ($gateways as $gateway) {
            Log::info("Processing gateway {$gateway->name} with remaining day: {$gateway->remaining_day}");
            $remainingDays = $gateway->remaining_day;

            // Query untuk masing-masing kategori tagihan
            $billingsPastDue = Billing::query()
                ->select('customer_id', 'customer_name', DB::raw('COUNT(*) as total_bills'), DB::raw('SUM(paket_price) as total_amount'))
                ->where(function ($query) use ($today) {
                    $query->where('status', 'BL')
                        ->whereNotNull('due_date')
                        ->whereDate('due_date', '<', $today)
                        ->orWhere(function ($q) use ($today) {
                            $q->where('status', 'PL')
                                ->whereNotNull('pay_later')
                                ->whereDate('pay_later', '<', $today);
                        });
                })
                ->groupBy('customer_name', 'customer_id')
                ->cursor();

            $billingsDueToday = Billing::query()
                ->select('customer_id', 'customer_name', DB::raw('COUNT(*) as total_bills'), DB::raw('SUM(paket_price) as total_amount'))
                ->where(function ($query) use ($today) {
                    $query->where('status', 'BL')
                        ->whereNotNull('due_date')
                        ->whereDate('due_date', $today)
                        ->orWhere(function ($q) use ($today) {
                            $q->where('status', 'PL')
                                ->whereNotNull('pay_later')
                                ->whereDate('pay_later', $today);
                        });
                })
                ->groupBy('customer_id', 'customer_name')
                ->cursor();

            $billingsUpcoming = Billing::query()
                ->select('customer_id', 'customer_name', DB::raw('COUNT(*) as total_bills'), DB::raw('SUM(paket_price) as total_amount'))
                ->where(function ($query) use ($today, $remainingDays) {
                    $query->where('status', 'BL')
                        ->whereNotNull('due_date')
                        ->whereRaw('DATEDIFF(due_date, ?) = ?', [$today, $remainingDays])
                        ->orWhere(function ($q) use ($today, $remainingDays) {
                            $q->where('status', 'PL')
                                ->whereNotNull('pay_later')
                                ->whereRaw('DATEDIFF(pay_later, ?) = ?', [$today, $remainingDays]);
                        });
                })
                ->groupBy('customer_id', 'customer_name')
                ->cursor();

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

        // Loop through the cursor and group data into batches
        foreach ($billings as $billing) {
            // Pastikan nomor kontak valid dan belum pernah terkirim
            $customer_billing = Billing::with('customer')->where('customer_name', $billing->customer_name)->first();
            $customer_contact = $this->manipulateContact($customer_billing);

            if (is_null($customer_contact)) {
                Log::info("Skipping billing ID: $customer_billing->id due to invalid or null contact.", [
                    'customer_name' => $customer_billing->customer_name,
                    'contact' => $customer_contact,
                ]);
                continue;
            }

            // Cek jika pesan sudah pernah dikirim ke nomor ini untuk jenis tagihan yang sama hari ini
            $alreadySent = MessageLog::where('customer_id', $billing->customer_id)
                ->where('message_type', $messageType)
                ->whereDate('created_at', Carbon::today())
                ->exists();

            if ($alreadySent) {
                Log::info("Skipping redundant message for customer ID: {$billing->customer_id} with type {$messageType}.");
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
            $customer_billing = Billing::with('customer')->where('customer_name', $billing->customer_name)->first();

            if ($customer_billing) {
                try {
                    // Simpan log sebelum mengirim
                    MessageLog::create([
                        'customer_id' => $customer_billing->customer_id,
                        'message_type' => $messageType,
                        'gateway_id' => $gateway->id,
                    ]);

                    // Kirim pesan
                    $this->messageService->bill_info($billing, $messageType);
                    Log::info("Message sent to customer name: {$billing->customer_name} with billing type {$messageType}", [
                        'customer_id' => $customer_billing->customer_id,
                        'customer_name' => $customer_billing->customer_name,
                        'due_date' => $customer_billing->due_date,
                        'pay_later' => $customer_billing->pay_later,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to send message for billing ID: {$customer_billing->id}. Error: {$e->getMessage()}");
                }
            } else {
                Log::warning("No customer billing found for customer name: {$billing->customer_name}");
            }
        }
    }

    private function manipulateContact($billing)
    {
        $customer_contact = $billing->customer->contact;

        if (is_null($customer_contact) || trim($customer_contact) === '') {
            return null;
        }

        if (!is_numeric($customer_contact)) {
            return null;
        }

        if (substr($customer_contact, 0, 1) == '0') {
            $customer_contact = '62' . substr($customer_contact, 1);
        }

        return $customer_contact;
    }
}
