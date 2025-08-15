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
        $startTime = microtime(true);
        $traceId = $this->traceId;
        $timestamp = now();

        LogHelper::logInfo(
            'SendWhatsappMessageJob',
            [
                'message_type' => $this->messageType,
                'receiver'     => $this->receiver,
                'customer_id'  => $this->customerId,
                'trace_id'     => $traceId,
            ],
            200,
            "Job started at {$timestamp}",
            $traceId
        );

        $gateway = WhatsappGateway::where('disabled', false)->firstOrFail();

        $customer = $this->customerId ? Customer::find($this->customerId) : null;
        $customerName = $customer?->name;

        $formatter = new FormatReceiver();
        $formattedReceiver = $formatter->formatReceiver($this->receiver, null, true);

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
            $client = new Client($this->buildHttpOptions($gateway));

            $response = $client->post($url, [
                'json' => [
                    'phone'   => $formattedReceiver,
                    'message' => $this->message,
                ],
            ]);

            $decoded = json_decode($response->getBody()->getContents(), true);
            $code = $decoded['code'] ?? null;
            $messageId = $decoded['results']['message_id'] ?? null;

            if ($code === 'SUCCESS' && $messageId) {
                LogHelper::logInfo($url, array_merge($payload, [
                    'message_id' => $messageId,
                    'message_type' => $this->messageType,
                ]), 200, "Message sent successfully at {$timestamp}", $traceId);

                MessageLog::create([
                    'customer_id'  => $customer?->id,
                    'message_type' => $this->messageType,
                    'gateway_id'   => $gateway->id,
                    'content'      => $this->message,
                    'sent_at'      => $timestamp,
                ]);
            } else {
                $this->logMessageError(
                    $gateway,
                    $customer,
                    $code,
                    $decoded['message'] ?? 'Unknown error',
                    $decoded,
                    $url,
                    $payload,
                    $traceId,
                    $timestamp
                );
            }
        } catch (RequestException $e) {
            $body = optional($e->getResponse())->getBody()?->getContents();
            $decoded = $body ? json_decode($body, true) : [];
            $messageError = $decoded['message'] ?? $e->getMessage();

            $this->logMessageError(
                $gateway,
                $customer,
                $e->getCode(),
                $messageError,
                ['raw_response' => $body],
                $url,
                $payload,
                $traceId,
                $timestamp
            );
        } catch (Exception $e) {
            $this->logMessageError(
                $gateway,
                $customer,
                $e->getCode(),
                $e->getMessage(),
                [
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage()
                ],
                $url,
                $payload,
                $traceId,
                $timestamp
            );
        }

        $executionTime = round(microtime(true) - $startTime, 2);
        LogHelper::logInfo(
            'SendWhatsappMessageJob',
            [
                'message_type'    => $this->messageType,
                'receiver'        => $this->receiver,
                'execution_time_s' => $executionTime,
                'trace_id'        => $traceId,
            ],
            200,
            "Job finished in {$executionTime} seconds",
            $traceId
        );
    }

    protected function buildHttpOptions($gateway): array
    {
        $options = ['verify' => false];

        if ($gateway->auth_disabled === "true") {
            $username = $gateway->basic_auth_username;
            $password = Crypt::decryptString($gateway->basic_auth_password);

            if (!$username || !$password) {
                throw new Exception("Basic Auth credentials are missing or invalid.");
            }

            $options['auth'] = [$username, $password];
        }

        return $options;
    }

    protected function logMessageError($gateway, $customer, $code, $errorMessage, $extraPayload, $url, $basePayload, $traceId, $timestamp)
    {
        MessageLogError::create([
            'customer_id'   => $customer?->id,
            'message_type'  => $this->messageType,
            'message_error' => $errorMessage,
            'gateway_id'    => $gateway->id,
            'content'       => $this->message,
            'sent_at'       => $timestamp,
        ]);

        LogHelper::logError($url, array_merge($basePayload, $extraPayload), $code, "WhatsApp Error: {$errorMessage}", $traceId);
    }
}
