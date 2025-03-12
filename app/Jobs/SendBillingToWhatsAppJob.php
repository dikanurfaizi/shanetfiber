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
            $gateway = WhatsappGateway::where('disabled', false)->get();

            if ($gateway->isEmpty()) {
                LogHelper::logWarning(
                    null,
                    null,
                    404, // Not Found
                    "No active WhatsApp gateway found."
                );
                return; // Berhenti jika tidak ada gateway
            }

            $lastBillingPeriode = BillingPeriode::latest('created_at')->first();

            if (!$lastBillingPeriode) {
                LogHelper::logWarning(
                    null,
                    null,
                    404, // Not Found
                    "No billing period found."
                );
                return; // Berhenti jika tidak ada billing periode
            }

            $billings = Billing::query()
                ->join('customers', 'billings.customer_id', '=', 'customers.id')
                ->leftJoin('users', 'customers.user_id', '=', 'users.id') // LEFT JOIN ke tabel users
                ->select(
                    'billings.customer_id', // ID pelanggan dari billings
                    'customers.customer_id as customer_unique_id', // ID pelanggan unik dari customers
                    'customers.name as customer_name', // Nama pelanggan
                    'customers.contact as customer_contact', // Kontak pelanggan
                    'customers.address as customer_address', // Alamat pelanggan
                    'billings.due_date', // Tanggal jatuh tempo dari billings
                    'users.name as admin_name', // Username dari users
                    DB::raw('COUNT(billings.id) as total_bills'), // Jumlah tagihan
                    DB::raw('SUM(billings.paket_price) as total_amount') // Total harga tagihan
                )
                ->whereNotNull('customers.contact') // Kontak tidak boleh null
                ->where('customers.contact', '!=', '') // Kontak tidak boleh kosong
                ->where(function ($query) {
                    $query->where('billings.status', 'BL') // Status belum lunas
                        ->whereNotNull('billings.due_date'); // due_date harus ada
                })
                ->orWhere(function ($query) {
                    $query->where('billings.status', 'PL') // Status pay later
                        ->whereNotNull('billings.pay_later'); // pay_later harus ada
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

            // Karena pakai cursor(), kita cek dengan cara beda
            if ($billings->isEmpty() || $billings->count() === 0) {
                LogHelper::logWarning(
                    null,
                    [
                        'periode_id' => $lastBillingPeriode->id
                    ],
                    404, // Not Found
                    "No billing data found."
                );
                return; // Berhenti jika tidak ada data billing
            }

            $this->processBillings($billings, 'create_new_bill');
        } catch (RequestException $e) {
            // Tangkap dan log error spesifik terkait permintaan HTTP
            LogHelper::logError(
                null,
                [
                    'billing_id' => null,
                    'customer_id' => null,
                ],
                500, // Exception
                "HTTP Request Exception: " . $e->getMessage()
            );
        } catch (\Exception $e) {
            // Tangkap error lainnya
            LogHelper::logError(
                null,
                [
                    'billing_id' => null,
                    'customer_id' => null,
                ],
                500, // Exception
                "Exception occurred while creating billing: " . $e->getMessage()
            );
        }
    }

    private function processBillings($billings, $messageType)
    {
        $batchSize = 50; // Ukuran batch yang diinginkan
        $batch = [];

        foreach ($billings as $billing) {
            // Log::info("Processing billing for {$billing->customer_name} with Message Type {$messageType}");

            $batch[] = $billing;

            // Jika batch sudah penuh, kirim pesan dalam batch
            if (count($batch) >= $batchSize) {
                $this->sendMessagesInBatch($batch, $messageType);
                $batch = []; // Reset batch setelah pengiriman
            }
        }

        // Kirim sisa batch jika ada
        if (count($batch) > 0) {
            $this->sendMessagesInBatch($batch, $messageType);
        }
    }

    private function sendMessagesInBatch($batch, $messageType)
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
                        "No customer billing found in batch processing."
                    );
                    continue;
                }

                try {
                    // Kirim pesan
                    $this->messageService->bill_info($billing, $messageType);

                    // Log sukses setelah berhasil mengirim pesan
                    LogHelper::logInfo(
                        null,
                        [
                            'customer_name' => $billing->customer_name,
                            'customer_contact' => $billing->customer_contact,
                            'message_type' => $messageType
                        ],
                        200, // OK
                        "WhatsApp message creation successful and in the process of sending a request to the messaging service"
                    );
                } catch (\Exception $e) {
                    LogHelper::logError(
                        null,
                        null,
                        500, // Internal Server Error
                        "Failed to send WhatsApp message to {$billing->customer_name}. Error: {$e->getMessage()}"
                    );
                }
            }

            // Tunggu sebelum mengirim batch berikutnya
            sleep($pauseDuration);
        }
    }
}
