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
use GuzzleHttp\Exception\RequestException;
//use Spatie\SslCertificate\SslCertificate;

class SendWhatsappSingleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $receiver;
    private string $message;
    // private string $gateway_number;
    private WhatsappGateway $gateway;
    //private string $secretId;
    /**
     * Create a new job instance.
     */
    // public function __construct(string $receiver, string $gateway_number, string $message)
    public function __construct(string $receiver, WhatsappGateway $gateway, string $message)
    {
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
        // Ambil informasi dari gateway
        $apikey = $this->gateway->api_key;
        //$certfile = $this->gateway->cert_file;
        $url = $this->gateway->url_callback;
        $sender_number = $this->gateway->whatsapp_number->number;
        $formattedReceiver = $this->receiver . '@s.whatsapp.net';

        // Buat payload hanya untuk body
        $payload = [
            'phone' => $formattedReceiver,
            'message' => $this->message,
        ];

        try {
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

            // Log keberhasilan pengiriman
            Log::info('Message sent successfully.', [
                'receiver' => $this->receiver,
                'sender' => $sender_number,
                'response_status' => $response->getStatusCode(),
                'response_body' => $response->getBody()->getContents(),
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Tangani error jika request gagal
            Log::error('Failed to send message.', [
                'receiver' => $this->receiver,
                'sender' => $sender_number,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ]);
        }
    }
}
