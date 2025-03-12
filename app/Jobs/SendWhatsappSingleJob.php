<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Crypt;

class SendWhatsappSingleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $customerName;
    private string $receiver;
    private string $message;
    private WhatsappGateway $gateway;

    public function __construct(string $customerName, string $receiver, WhatsappGateway $gateway, string $message)
    {
        $this->customerName = $customerName;
        $this->receiver = $this->formatReceiver($receiver);
        $this->gateway = $gateway;
        $this->message = $message;
    }

    public function handle(): void
    {
        $urlWithParams = "{$this->gateway->url_callback}/send/message?api_key={$this->gateway->api_key}&sender={$this->gateway->whatsapp_number->number}";

        try {
            $decryptPassword = Crypt::decryptString($this->gateway->basic_auth_password);
            $client = new Client([
                'auth' => [
                    $this->gateway->basic_auth_username,
                    $decryptPassword
                ],
                'verify' => false
            ]);

            $response = $client->post($urlWithParams, [
                'json' => [
                    'phone' => $this->receiver,
                    'message' => $this->message,
                ],
            ]);

            $this->logResponse($response->getBody()->getContents(), $urlWithParams);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->logError($e, $urlWithParams);
        } catch (\Exception $e) {
            $this->logUnexpectedError($e, $urlWithParams);
        }
    }

    private function formatReceiver(string $receiver): string
    {
        if (preg_match('/^62(\d{9,15})$/', $receiver, $matches)) {
            $numberLength = strlen($matches[1]);
            $prefix = $numberLength === 9 ? "+" : "";
            return "{$prefix}62{$matches[1]}@s.whatsapp.net";
        }
        return "$receiver@s.whatsapp.net";
    }

    private function logResponse(string $responseBody, string $url): void
    {
        $decoded = json_decode($responseBody, true);
        $messageId = $decoded['results']['message_id'] ?? null;
        $formattedTime = now()->format('d-m-Y H:i:s');
        $message = "Message sent successfully with ID $messageId on $formattedTime";

        LogHelper::logInfo($url, [
            'receiver' => $this->receiver,
            'customer_name' => $this->customerName,
            'sender' => $this->gateway->whatsapp_number->number,
        ], 200, $message);
    }

    private function logError(\GuzzleHttp\Exception\RequestException $e, string $url): void
    {
        $response = method_exists($e, 'getResponse') && $e->getResponse()
            ? $e->getResponse()->getBody()->getContents()
            : null;
        $decoded = $response ? json_decode($response, true) : [];
        $message = $decoded['message'] ?? $e->getMessage();

        LogHelper::logError($url, [
            'receiver' => $this->receiver,
            'customer_name' => $this->customerName,
            'sender' => $this->gateway->whatsapp_number->number,
        ], $e->getCode(), "Failed to send message: $message");
    }

    private function logUnexpectedError(\Exception $e, string $url): void
    {
        LogHelper::logError($url, [
            'receiver' => $this->receiver,
            'customer_name' => $this->customerName,
            'sender' => $this->gateway->whatsapp_number->number,
        ], $e->getCode(), "Unexpected error: {$e->getMessage()}");
    }
}
