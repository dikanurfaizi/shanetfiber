<?php

namespace App\Jobs\WhatsApp;

use App\Models\Customer;
use App\Models\MessageLog;
use App\Models\MessageLogError;
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
use GuzzleHttp\Exception\RequestException;
use Exception;

class SendWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $messageType,
        private string $receiver,
        private string $message,
        private ?string $customerId,
        private string $traceId
    ) {}

    public function handle(): void
    {
        $traceId = $this->traceId;
        $gateway = WhatsappGateway::where('disabled', false)->firstOrFail();

        if (!$gateway) {
            LogHelper::logError('No Active Gateway', [
                'receiver' => $this->receiver,
                'customer_id' => $this->customerId,
            ], 404, "No active WhatsApp gateway found", $this->traceId);
            return;
        }

        $customer = null;
        $customerName = null;

        if ($this->customerId) {
            $customer = Customer::find($this->customerId);
            $customerName = $customer?->name;
        }

        $formatter = new FormatReceiver();
        $formattedReceiver = $formatter->formatReceiver($this->receiver, null, true);
        $timestamp = now();
        $url = "{$gateway->url_callback}/send/message";

        $payload = [
            'customer_id'   => $customer?->id,
            'customer_name' => $customerName,
            'receiver'      => $formattedReceiver,
            'sender'        => $gateway->whatsapp_number->number,
        ];

        if (!$this->customerId) {
            LogHelper::logInfo('SendWhatsappMessageJob', [
                'receiver' => $this->receiver,
                'message' => $this->message,
            ], 200, "No customer ID provided, sending without customer reference", $traceId);
        }

        try {
            $options = [
                'verify' => false
            ];

            if ($gateway->auth_disabled === "true") {
                try {
                    $username = $gateway->basic_auth_username;
                    $decryptedPassword = Crypt::decryptString($gateway->basic_auth_password);

                    if (!$username || !$decryptedPassword) {
                        throw new Exception("Basic Auth credentials are missing or invalid.");
                    }

                    $options['auth'] = [$username, $decryptedPassword];
                } catch (Exception $e) {
                    LogHelper::logError($url, array_merge($payload, [
                        'error' => $e->getMessage(),
                    ]), 403, "Invalid or missing basic auth credentials", $traceId);
                    return;
                }
            }

            $client = new Client($options);

            LogHelper::logInfo($url, [
                'payload' => [
                    'phone'   => $formattedReceiver,
                    'message' => $this->message,
                ]
            ], 200, "Preparing to send WhatsApp message", $traceId);

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
                LogHelper::logInfo($url, array_merge($payload, ['message_id' => $messageId, 'message_type' => $this->messageType]), 200, "Message sent successfully at {$timestamp}", $traceId);

                MessageLog::create([
                    'customer_id'  => $customer?->id,
                    'message_type' => $this->messageType,
                    'gateway_id'   => $gateway->id,
                    'content'      => $this->message,
                    'sent_at'      => $timestamp,
                ]);
            } else {
                $errorMessage = isset($decoded['message']) ? $decoded['message'] : 'Unknown error';

                MessageLogError::create([
                    'customer_id'  => $customer?->id,
                    'message_type' => $this->messageType,
                    'message_error' => $errorMessage,
                    'gateway_id'   => $gateway->id,
                    'content'      => $this->message,
                    'sent_at'      => $timestamp,
                ]);

                LogHelper::logError($url, array_merge($payload, [
                    'code'     => $code,
                    'response' => $decoded
                ]), 401, "WhatsApp Gateway Error: {$errorMessage}", $traceId);
            }
        } catch (RequestException $e) {
            $body = optional($e->getResponse())->getBody()?->getContents();
            $decoded = $body ? json_decode($body, true) : [];
            $messageError = $decoded['message'] ?? $e->getMessage();

            LogHelper::logError($url, array_merge($payload, [
                'raw_response' => $body
            ]), $e->getCode(), "Failed to send message: $messageError", $traceId);
        } catch (Exception $e) {
            LogHelper::logError($url, $payload, $e->getCode(), "Unexpected error: {$e->getMessage()}", $traceId);
        }
    }
}
