<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    /**
     * Create a new job instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $apiUrl = config('whatsapp.api_url');
        $contacts = Customer::pluck('contact')->toArray();
        $message = $this->message; // Mengambil pesan yang dikirim dari controller

        // Log URL API untuk memastikan sudah sesuai
        if ($apiUrl) {
            Log::info("API URL retrieved successfully: $apiUrl");
        } else {
            Log::error("API URL is missing or invalid in configuration.");
            return; // Keluar dari job jika URL tidak ditemukan
        }

        Log::info("Starting WhatsApp message sending job with message: {$message}");

        foreach ($contacts as $contact) {
            // Format nomor kontak
            $formattedPhone = $this->formatPhoneNumber($contact);

            // Skip kontak yang tidak berhasil diformat
            if (is_null($formattedPhone)) {
                Log::warning("Skipping invalid contact: $contact");
                continue;
            }

            // Log untuk memverifikasi nomor yang telah diformat
            Log::info("Formatted phone number: $formattedPhone (original: $contact)");

            Log::info("Attempting to send message to $formattedPhone");

            $body = [
                'phone' => "{$formattedPhone}@s.whatsapp.net",
                'message' => $message
            ];

            try {
                $response = Http::post($apiUrl, $body);
                if ($response->successful()) {
                    Log::info("Message successfully sent to $formattedPhone.");
                } else {
                    Log::warning("Failed to send message to $formattedPhone. Response: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Exception occurred while sending message to $formattedPhone: " . $e->getMessage());
            }
        }

        Log::info("WhatsApp message sending job completed.");
    }

    /**
     * Format phone number to the international format required by WhatsApp.
     *
     * @param string $contact
     * @return string|null
     */
    private function formatPhoneNumber($contact)
    {
        if (is_null($contact) || !is_string($contact)) {
            Log::warning("Contact is null or not a valid string: " . json_encode($contact));
            return null; // Skip if contact is null or not a valid string
        }

        if (str_starts_with($contact, '08')) {
            return '62' . substr($contact, 1);
        } elseif (str_starts_with($contact, '8')) {
            return '62' . $contact;
        } elseif (str_starts_with($contact, '62')) {
            return $contact;
        } else {
            Log::warning("Invalid contact format for $contact.");
            return null; // Skip if format is unrecognized
        }
    }
}
