<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\MessageLog;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Helpers\LogHelper;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\FormatReceiver;

class SendWhatsappSingleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $messageType,
        private string $receiver,
        private string $message,
        private string $customerId
    ) {}

    public function handle(): void
    {
        $gateway = WhatsappGateway::where('disabled', false)
            ->firstOrFail();
        $customer = Customer::findOrFail($this->customerId);

        $formatter = new FormatReceiver();
        $formattedReceiver = $formatter->formatReceiver($this->receiver, 'broadcast');
        $timestamp = now();
        $url = "{$gateway->url_callback}/send/message?api_key={$gateway->api_key}&sender={$gateway->whatsapp_number->number}";

        $payload = [
            'customer_id'   => $customer->id,
            'customer_name' => $customer->name,
            'receiver'      => $formattedReceiver,
            'sender'        => $gateway->whatsapp_number->number,
        ];

        try {
            // $client = new Client([
            //     'auth'   => [$gateway->basic_auth_username, Crypt::decryptString($gateway->basic_auth_password)],
            //     'verify' => false
            // ]);

            $options = [
                'verify' => false
            ];

            if ($gateway->auth_disabled === "true") {
                $options['auth'] = [
                    $gateway->basic_auth_username,
                    Crypt::decryptString($gateway->basic_auth_password)
                ];
            }

            $client = new Client($options);

            $response = $client->post($url, [
                'json' => [
                    'phone'   => $formattedReceiver,
                    'message' => $this->message,
                ],
            ]);

            $decoded = json_decode($response->getBody()->getContents(), true);
            $code = $decoded['code'] ?? 'ERROR';
            $messageId = $decoded['results']['message_id'] ?? null;

            if ($code === 'SUCCESS' && $messageId) {
                LogHelper::logInfo($url, array_merge($payload, ['message_id' => $messageId, 'message_type' => $this->messageType]), 200, "Message sent successfully at {$timestamp}");

                MessageLog::create([
                    'customer_id'  => $customer->id,
                    'message_type' => $this->messageType,
                    'gateway_id'   => $gateway->id,
                    'content'      => $this->message,
                    'sent_at'      => $timestamp,
                ]);
            } else {
                $errorMessage = isset($decoded['message']) ? $decoded['message'] : 'Unknown error';

                LogHelper::logError($url, array_merge($payload, [
                    'code'     => $code,
                    'response' => $decoded
                ]), 401, "WhatsApp Gateway Error: {$errorMessage}");
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = optional($e->getResponse())->getBody()?->getContents();
            $decoded = $body ? json_decode($body, true) : [];
            $messageError = $decoded['message'] ?? $e->getMessage();

            LogHelper::logError($url, array_merge($payload, [
                'raw_response' => $body
            ]), $e->getCode(), "Failed to send message: $messageError");
        } catch (\Exception $e) {
            LogHelper::logError($url, $payload, $e->getCode(), "Unexpected error: {$e->getMessage()}");
        }
    }
}
