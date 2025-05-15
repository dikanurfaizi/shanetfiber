<?php

namespace App\Jobs;

use App\Models\Billing\Billing;
use App\Models\MessageLog;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Services\WhatsappMessageService;
use App\Helpers\LogHelper;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappSingleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $billingId,
        private string $messageType,
        private int $gatewayId
    ) {}

    public function handle(): void
    {
        $billing = Billing::findOrFail($this->billingId);

        $messageService = app(WhatsappMessageService::class);

        // Skip jika sudah pernah dikirim hari ini
        $alreadySent = MessageLog::where('customer_id', $billing->customer_id)
            ->where('message_type', $this->messageType)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if ($alreadySent) {
            LogHelper::logWarning(null, [
                'customer_name' => $billing->customer_name,
                'message_type'  => $this->messageType,
            ], 409, "Skipping redundant message for {$billing->customer_name} with type {$this->messageType}.");
            return;
        }

        $message = $messageService->bill_info($billing, $this->messageType);

        if ($message) {
            try {
                $this->sendMessage(
                    $billing->customer_id,
                    $billing->customer_name,
                    $billing->customer->contact,
                    $this->gatewayId,
                    $message
                );
            } catch (\Exception $e) {
                LogHelper::logError(null, [
                    'billing_id'     => $billing->id ?? null,
                    'customer_id'    => $billing->customer_id ?? null,
                    'customer_name'  => $billing->customer_name ?? null,
                    'message_type'   => $this->messageType,
                    'error_message'  => $e->getMessage(),
                ], 500, "Exception while sending WhatsApp message.");
            }
        }
    }

    private function sendMessage($customerId, $customerName, $receiver, $gatewayId, $message): void
    {
        $gateway = WhatsappGateway::findOrFail($gatewayId);
        $url = "{$gateway->url_callback}/send/message?api_key={$gateway->api_key}&sender={$gateway->whatsapp_number->number}";

        try {
            $client = new Client([
                'auth' => [
                    $gateway->basic_auth_username,
                    Crypt::decryptString($gateway->basic_auth_password),
                ],
                'verify' => false
            ]);

            $formattedReceiver = $this->formatReceiver($receiver);

            $response = $client->post($url, [
                'json' => [
                    'phone'   => $formattedReceiver,
                    'message' => $message,
                ],
            ]);

            $decoded = json_decode($response->getBody()->getContents(), true);
            $code = $decoded['code'] ?? 'ERROR';
            $messageId = $decoded['results']['message_id'] ?? null;
            $timestamp = now()->format('d-m-Y H:i:s');

            if ($code === 'SUCCESS' && $messageId) {
                LogHelper::logInfo($url, [
                    'customer_id'    => $customerId,
                    'customer_name'  => $customerName,
                    'receiver'       => $receiver,
                    'sender'         => $gateway->whatsapp_number->number,
                    'message_id'     => $messageId,
                ], 200, "Message sent successfully with ID $messageId on $timestamp");

                MessageLog::create([
                    'customer_id'   => $customerId,
                    'message_type'  => $this->messageType,
                    'gateway_id'    => $gateway->id,
                    'content'       => $message,
                    'sent_at'       => now(),
                ]);
            } else {
                // Log error detail untuk investigasi
                LogHelper::logError($url, [
                    'customer_id'    => $customerId,
                    'customer_name'  => $customerName,
                    'receiver'       => $receiver,
                    'sender'         => $gateway->whatsapp_number->number,
                    'code'           => $code,
                    'response'       => $decoded,
                ], 401, "WhatsApp Gateway Error: {$decoded['message']}");
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = optional($e->getResponse())->getBody()?->getContents();
            $decoded = $responseBody ? json_decode($responseBody, true) : [];
            $messageError = $decoded['message'] ?? $e->getMessage();

            LogHelper::logError($url, [
                'customer_id'    => $customerId,
                'customer_name'  => $customerName,
                'receiver'       => $receiver,
                'sender'         => $gateway->whatsapp_number->number,
                'raw_response'   => $responseBody,
            ], $e->getCode(), "Failed to send message: $messageError");
        } catch (\Exception $e) {
            LogHelper::logError($url, [
                'customer_id'    => $customerId,
                'customer_name'  => $customerName,
                'receiver'       => $receiver,
                'sender'         => $gateway->whatsapp_number->number,
            ], $e->getCode(), "Unexpected error: {$e->getMessage()}");
        }
    }

    private function formatReceiver(string $contact): string|null
    {
        try {
            $contact = preg_replace('/[^\d+]/', '', $contact);

            if (str_starts_with($contact, '+62')) {
                $contact = substr($contact, 1);
            }

            if (str_starts_with($contact, '08')) {
                $contact = '62' . substr($contact, 1);
            }

            if (str_starts_with($contact, '62')) {
                $numberOnly = substr($contact, 2);
                return strlen($numberOnly) === 9 ? '+62' . $numberOnly : '62' . $numberOnly;
            }

            return $contact;
        } catch (\Exception $e) {
            LogHelper::logWarning(null, null, 500, "Error formatting contact: $contact | " . $e->getMessage());
            return null;
        }
    }
}
