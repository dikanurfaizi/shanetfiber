<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
// use Illuminate\Support\Carbon;
// use App\Models\Billing\Billing;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
// use App\Models\WhatsappGateway\WhatsappNumber;
// use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Models\Customer;
use GuzzleHttp\Exception\RequestException;
//use Spatie\SslCertificate\SslCertificate;

class SendWhatsappSingleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $customer_name;
    private string $receiver;
    private string $message;
    // private string $gateway_number;
    private WhatsappGateway $gateway;
    //private string $secretId;
    /**
     * Create a new job instance.
     */
    // public function __construct(string $receiver, string $gateway_number, string $message)
    public function __construct(string $customer_name, string $receiver, WhatsappGateway $gateway, string $message)
    {
        $this->customer_name = $customer_name;
        $this->receiver = $receiver;
        //$this->gateway_number = $gateway_number;
        $this->gateway = $gateway;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    // public function handle(): void
    // {
    //     // $whatsappGateway = WhatsappGateway::where('slug', 'gateway')->first();
    //     // $apikey = $whatsappGateway->api_key;
    //     // $certfile = $whatsappGateway->cert_file;
    //     //  $url = $whatsappGateway->url_callback;
    //     $apikey = $this->gateway->api_key;
    //     $certfile = $this->gateway->cert_file;
    //     $url = $this->gateway->url_callback;
    //     //$send_wa = $url . '/send-message?api_key=' . $apikey . '&sender=' . $this->gateway_number . '&number=' . $this->receiver . '&message=' . $this->message;
    //     $send_wa = $url . '/send-message?api_key=' . $apikey . '&sender=' . $this->gateway->whatsapp_number->number . '&number=' . $this->receiver . '&message=' . $this->message;
    //     $client = new Client();
    //     $client->request('GET', $send_wa, ['verify' => $certfile]);
    //     Log::info('Receiver Number => ' . $this->receiver . ', Sender Number => ' . $this->gateway->whatsapp_number->number . ', Message => ' . $this->message);
    // }

    public function handle(): void
    {
        try {
            // Ambil informasi dari gateway
            $apikey = $this->gateway->api_key;
            //$certfile = $this->gateway->cert_file;
            $url = $this->gateway->url_callback;
            $sender_number = $this->gateway->whatsapp_number->number;
            $formattedReceiver = $this->receiver . '@s.whatsapp.net';
            $customer_name = $this->customer_name;

            // Buat payload hanya untuk body
            $payload = [
                'phone' => $formattedReceiver,
                'message' => $this->message,
            ];

            // Log::info('Trying to sent message.', [
            //     'receiver' => $this->receiver,
            //     'sender' => $sender_number,
            // ]);
            // Buat instance Guzzle HTTP Client
            // $client = new Client([
            //     'verify' => $certfile, // Sertifikat SSL jika diperlukan
            // ]);
            $client = new Client();

            // Buat URL dengan query parameters
            $url_with_params = "{$url}/send/message?api_key={$apikey}&sender={$sender_number}";

            // Kirim POST request dengan payload
            $response = $client->post($url_with_params, [
                'json' => $payload, // Mengirim payload dalam format JSON
            ]);

            $responseBody = $response->getBody()->getContents(); // Ambil body JSON dari response
            $decodedResponse = json_decode($responseBody, true); // Decode ke array asosiatif

            // Ambil informasi dari JSON
            $timestamp = $decodedResponse['message'] ? substr($decodedResponse['message'], strpos($decodedResponse['message'], '(server timestamp:') + 19, 20) : 'Unknown time';

            // Log keberhasilan pengiriman
            Log::info('Message sent to', [
                'receiver' => $this->receiver,
                'customer_name' => $customer_name,
                'sender' => $sender_number,
                'status_code' => $response->getStatusCode(),
                'server_time' => $timestamp,
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Tangani error jika request gagal
            $error = $e->getMessage(); // Mengambil pesan error lengkap
            $response = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

            // Ekstrak status error
            preg_match('/\`(\d{3} [A-Za-z\s]+)\`/', $error, $statusMatch);
            $errorStatus = $statusMatch[1] ?? 'Unknown error status';

            // Ekstrak pesan dari JSON
            $decodedResponse = $response ? json_decode($response, true) : [];
            $message = $decodedResponse['message'] ?? 'Unknown message';

            // Hapus domain @s.whatsapp.net dari message
            $formattedMessage = preg_replace('/@s\.whatsapp\.net/', '', $message);

            Log::error('Failed to send message.', [
                'receiver' => $this->receiver,
                'customer_name' => $customer_name,
                'sender' => $sender_number,
                'status_code' => $errorStatus,
                'response' => $formattedMessage,
            ]);
        }
    }
}
