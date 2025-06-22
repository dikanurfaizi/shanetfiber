<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use App\Models\WhatsappGateway\WhatsappNumber;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Models\WhatsappGateway\WhatsappMessage;
use App\Models\User;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\Billing\Billing;
use App\Models\MessageLog;
use App\Models\MessageLogError;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Crypt;
use GuzzleHttp\Client;
use App\Helpers\FormatReceiver;
use Illuminate\Support\Str;

class WhatsappMessageService
{
    /**
     * Chesk status wa gateway
     * return false = enable, return true = disable
     */

    private FormatReceiver $formatter;

    public function __construct()
    {
        $this->formatter = new FormatReceiver();
    }

    public function sendWhatsappSingleJob($customerId, $customerName, $receiver, $gateway, $message, $messageType, $traceId, $test)
    {
        $sender = optional($gateway->whatsapp_number)->number;

        if (!$sender) {
            LogHelper::logError(null, ['gateway_id' => $gateway->id], 400, "No WhatsApp number found for gateway", $traceId);
            return false;
        }

        $urlWithParams = "{$gateway->url_callback}/send/message?api_key={$gateway->api_key}&sender={$sender}";

        try {
            $alreadySent = MessageLog::where('customer_id', $customerId)
                ->where('message_type', $messageType)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($alreadySent) {
                LogHelper::logWarning(null, [
                    'customer_name' => $customerName,
                    'message_type'  => $messageType,
                ], 409, "Skipping redundant message for {$customerName} with type {$messageType}.");
                return;
            }

            $decryptPassword = Crypt::decryptString($gateway->basic_auth_password);
            $client = new Client([
                'auth' => [
                    $gateway->basic_auth_username,
                    $decryptPassword
                ],
                'verify' => false
            ]);

            $formattedReceiver = $this->formatter->formatReceiver($receiver, 'broadcast');

            $payload = [
                'customer_name' => $customerName,
                'receiver'      => $formattedReceiver,
                'sender'        => $sender,
            ];

            $timestamp = now();
            $response = $client->post($urlWithParams, [
                'json' => [
                    'phone'   => $formattedReceiver,
                    'message' => $message,
                ],
            ]);

            $decoded = json_decode($response->getBody()->getContents(), true);
            $results = $decoded['results'] ?? [];
            $messageId = is_array($results) ? ($results['message_id'] ?? null) : null;
            $code = $decoded['code'] ?? 'ERROR';

            if ($code === 'SUCCESS' && $messageId) {
                LogHelper::logInfo($urlWithParams, array_merge($payload, ['message_id' => $messageId, 'message_type' => $messageType]), 200, "Message sent successfully at {$timestamp}", $traceId);

                if (strtolower($test) !== 'yes') {
                    MessageLog::create([
                        'customer_id'  => $customerId,
                        'message_type' => $messageType,
                        'gateway_id'   => $gateway->id,
                        'content'      => $message,
                        'sent_at'      => $timestamp,
                    ]);
                }
            } else {
                $errorMessage = isset($decoded['message']) ? $decoded['message'] : 'Unknown error';

                LogHelper::logError($urlWithParams, array_merge($payload, [
                    'code'     => $code,
                    'response' => $decoded
                ]), 401, "WhatsApp Gateway Error: {$errorMessage}", $traceId);
            }

            return true;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $timestamp = now();
            $response = method_exists($e, 'getResponse') && $e->getResponse()
                ? $e->getResponse()->getBody()->getContents()
                : null;
            $decoded = $response ? json_decode($response, true) : [];
            $error_message = $decoded['message'] ?? $e->getMessage();

            MessageLogError::create([
                'customer_id'  => $customerId,
                'message_type' => $messageType,
                'gateway_id'   => $gateway->id,
                'message_error' => $error_message,
                'sent_at'      => $timestamp,
                'content'       => $message,
            ]);

            LogHelper::logError($urlWithParams, [
                'receiver' => $receiver,
                'customer_name' => $customerName,
                'sender' => $sender,
                'response_code' => $e->getCode(),
                'response' => $response,
            ], $e->getCode(), "Failed to send message: $error_message", $traceId);

            return false;
        } catch (\Exception $e) {
            LogHelper::logError($urlWithParams, [
                'receiver'       => $receiver,
                'customer_name'  => $customerName,
                'sender'         => $sender,
            ], $e->getCode(), "Unexpected error: {$e->getMessage()}", $traceId);

            return false;
        }
    }

    private function getGateway()
    {
        try {
            $gateway = WhatsappGateway::where('slug', 'gateway')->first();

            if (!$gateway || $gateway->disabled !== 0) {
                LogHelper::logWarning(
                    null,
                    null,
                    404,
                    "WhatsApp Gateway is either disabled or not found."
                );
                return "WhatsApp Gateway is either disabled or not found.";
            }

            return $gateway;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                null,
                500,
                "Error retrieving WhatsApp Gateway: " . $e->getMessage()
            );
            return null;
        }
    }

    public function getMessage($incoming_message)
    {
        try {
            return WhatsappMessage::where('incoming_message', $incoming_message)
                ->where('disabled', false)
                ->where('default', false)
                ->first();
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                null,
                500,
                "Error retrieving message for incoming message: $incoming_message | " . $e->getMessage()
            );
            return null;
        }
    }

    public function getDefaultMessage($slug)
    {
        try {
            return WhatsappMessage::where('slug', $slug)
                ->where('disabled', false)
                ->where('default', true)
                ->value('reply_message');
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                null,
                500,
                "Error retrieving default message for slug: $slug | " . $e->getMessage()
            );
            return "Pesan default tidak tersedia.";
        }
    }

    public function getCustomer($customer_number)
    {
        try {
            return Customer::where('contact', $customer_number)->first();
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                null,
                500,
                "Error retrieving customer for contact: $customer_number | " . $e->getMessage()
            );
            return null;
        }
    }

    /**
     * Ambil informasi bank dalam format string
     */
    public function getBankInfo($id)
    {
        try {
            // Ambil bank info berdasarkan user_id
            $banks = Bank::where('user_id', $id)
                ->where('disabled', 'false')
                ->select('name', 'bank', 'account_number')
                ->get();

            if ($banks->isEmpty()) {
                LogHelper::logWarning(
                    null,
                    null,
                    404,
                    "No bank information available for user_id: {$id}"
                );
                return 'Tidak ada informasi bank';
            }

            $bankInfo = $banks->map(fn($bank) => "- {$bank->bank} - {$bank->account_number} (a.n {$bank->name})")
                ->implode("\n");

            return $bankInfo;
        } catch (\Exception $e) {
            // Jika terjadi error
            LogHelper::logError(
                null,
                null,
                500, // Internal Server Error
                "Failed to retrieve bank information. Error: {$e->getMessage()}"
            );
            return 'Terjadi kesalahan saat mengambil informasi bank';
        }
    }

    /**
     * Ambil rincian paket pelanggan
     */
    public function getBillingPackages($cust_id)
    {
        try {
            $packages = Billing::with(['billing_periode', 'customer.paket', 'customer.member'])
                ->select('pakets.name as paket_name', 'billings.billing_periode_id', 'billings.paket_price', 'pakets.price', 'members.discount', 'members.type', 'billings.status', 'billings.due_date', 'billings.pay_later', 'billing_periodes.name as periode_name')
                ->leftJoin('customers', 'billings.customer_id', '=', 'customers.id')
                ->leftJoin('pakets', 'customers.paket_id', '=', 'pakets.id')
                ->leftJoin('members', 'customers.member_id', '=', 'members.id')
                ->leftJoin('billing_periodes', 'billings.billing_periode_id', '=', 'billing_periodes.id')
                ->where('billings.customer_id', $cust_id)
                ->whereIn('billings.status', ['BL', 'PL'])
                ->orderByRaw("CASE WHEN billings.status = 'BL' THEN billings.due_date ELSE billings.pay_later END DESC")
                ->get();

            if ($packages->isEmpty()) {
                LogHelper::logError(
                    null,
                    ['customer_id' => $cust_id],
                    404, // Bad Request
                    "No billing packages found for customer"
                );
            }

            return $packages;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['customer_id' => $cust_id],
                500, // Bad Request
                "Error retrieving billing packages: {$e->getMessage()}"
            );
            return collect(); // Return collection kosong agar tidak error saat diakses
        }
    }

    /**
     * Format informasi paket menjadi string
     */
    public function formatPaketInfo($paket)
    {
        try {
            $periode = optional($paket->billing_periode)->name ?? 'Tidak ada periode';
            $formattedPeriode = $periode !== 'Tidak ada periode'
                ? Carbon::parse("1 " . $periode)->locale('id')->translatedFormat('F Y')
                : $periode;

            $member = $paket->type ?? 'Non Member';
            $discount = isset($paket->discount) ? number_format($paket->discount, 2) : number_format(0, 2);

            return sprintf(
                "📦 *Paket:* %s\n📆 *Periode:* %s\n💶 *Harga Paket:* Rp. %s\n\n🔹 *Rincian Tagihan:*\n🌟 *Kategori:* %s\n💸 *Potongan Harga:* Rp. %s",
                $paket->paket_name ?? 'Tidak ada nama paket',
                $formattedPeriode,
                number_format($paket->price ?? 0, 2),
                $member,
                $discount
            );
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['paket' => $paket->paket_name ?? null],
                500,
                "Error formatting package info: {$e->getMessage()}"
            );
            return "Informasi paket tidak tersedia";
        }
    }

    /**
     * Ambil template pesan berdasarkan aksi
     */
    public function getMessageTemplate($action, $pakets, $admin)
    {
        try {
            $replace = ['%count-bill%' => $pakets->count()];

            switch ($action) {
                case 'warning_bill':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('past_due_warning'));
                case 'deadline_bill':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('deadline_bill'));
                case 'reminder_bill':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('reminder_bill'));
                case 'create_new_bill':
                    $firstPaket = $pakets->first();
                    $dueDate = $firstPaket && $firstPaket->due_date
                        ? Carbon::parse($firstPaket->due_date)->timezone('Asia/Jakarta')->locale('id')->isoFormat('D MMMM Y')
                        : 'Tidak ada jatuh tempo';

                    $replace['%deadline%'] = $dueDate;
                    $replace['%admin%'] = $admin;

                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('create_new_bill'));
                case 'activation_customer':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('activation_customer'));
                default:
                    LogHelper::logWarning(
                        null,
                        ['action' => $action],
                        400, // Bad Request
                        "Invalid action provided"
                    );
                    return null;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['action' => $action],
                500, // Bad Request
                "Error processing message template: {$e->getMessage()}"
            );
            return null;
        }
    }

    /**
     * Ambil nomor WhatsApp aktif
     */
    private function getWhatsAppNumber($id)
    {
        try {
            $user = User::where('id', $id)->first();
            $number = $user ? $user->contact : null;

            if (!$number) {
                LogHelper::logWarning(
                    null,
                    null,
                    404, // Bad Request
                    "No active WhatsApp number found"
                );
            }

            return $number;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                null,
                500, // Bad Request
                "Error retrieving WhatsApp number: {$e->getMessage()}"
            );

            return null;
        }
    }

    /**
     * Gantikan placeholder dalam pesan.
     */
    public function replaceMessagePlaceholders($message, array $replace)
    {
        try {
            return str_replace(array_keys($replace), array_values($replace), $message);
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'message' => $message,
                    'replace' => $replace
                ],
                500, // Bad Request
                "Error replacing message placeholders: {$e->getMessage()}"
            );
            return $message; // Jika gagal, return pesan asli agar tidak blank
        }
    }

    public function activation($customerId, $action, $traceId)
    {
        $admin_number = null;
        try {
            $gateway = $this->getGateway();
            $customer = Customer::find($customerId);
            $customer_contact = $customer->contact;

            if (!$gateway || !$customer_contact) {
                LogHelper::logWarning(
                    null,
                    [
                        'customer_id' => $customer->id,
                        'gateway' => $gateway ? 'Available' : 'Not Available',
                        'customer_contact' => $customer_contact ? 'Available' : 'Not Available',
                    ],
                    400, // Bad Request
                    "Gateway atau kontak pelanggan tidak tersedia untuk billing ID: {$customer->id}."
                );
                return;
            }

            $admin = User::where('id', $customer->user_id)->first() ?? 1;
            $admin_number = '-';

            if (!empty($admin->contact)) {
                $admin_number = $this->formatter->formatReceiver($admin->contact, null);
            }

            $accountBank = $this->getBankInfo($admin->id);

            $replace = [
                '%name%' => $customer->name,
                '%address%' => $customer->address,
                '%email%' => $customer->email ?? '-',
                '%customer_service%' => $admin_number,
                '%customer_id%' => $customer->customer_id,
                '%admin%' => $admin->name,
                '%paket%' => $customer->paket->name,
                '%bill%' => 'Rp. ' . number_format($customer->paket->price, 2, ',', '.'),
                '%account_bank%' => $accountBank,
            ];

            $message = $this->getDefaultMessage($action);
            if (!$message) {
                LogHelper::logWarning(
                    null,
                    [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'address' => $customer->address,
                        'email' => $customers->email ?? '-',
                        'customer_service' => $admin_number,
                        'customer_id' => $customer->customer_id,
                        'action' => $action,
                    ],
                    404, // Not Found
                    "Template pesan untuk aksi '{$action}' tidak ditemukan untuk customer ID: {$customer->id}"
                );
                return;
            }

            $message = $this->replaceMessagePlaceholders($message, $replace);
            $this->sendWhatsappSingleJob($customer->id, $customer->name, $customer_contact, $gateway, $message, $action, $traceId, null);
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'email' => $customer->email ?? '-',
                    'customer_service' => $admin_number,
                    'customer_id' => $customer->customer_id,
                    'action' => $action,
                ],
                500, // Internal Error
                "Terjadi kesalahan dalam {$action}: " . $e->getMessage()
            );
        }
    }

    public function create_order($billing, $order, $action)
    {
        $traceId = (string) Str::uuid();
        try {
            $gateway = $this->getGateway();
            $customer_contact = $billing->customer->contact;

            if (!$gateway || !$customer_contact) {
                LogHelper::logWarning(
                    null,
                    [
                        'billing_id' => $billing->id,
                        'gateway' => $gateway ? 'Available' : 'Not Available',
                        'customer_contact' => $customer_contact ? 'Available' : 'Not Available',
                    ],
                    400, // Bad Request
                    "Gateway atau kontak pelanggan tidak tersedia untuk billing ID: {$billing->id}."
                );
                return;
            }

            $userId = $billing->customer->user_id ?? 1;
            $whatsapp_number = $this->formatter->formatReceiver($this->getWhatsAppNumber($userId), 'broadcast');

            $paymentMethod = strtolower(str_replace(['-', ' '], '', $order->payment_type));

            $expiredTime = Carbon::createFromTimestamp($order->expired_time)
                ->locale('id')
                ->translatedFormat('d F Y H:i') . ' WIB';

            $formattedAmount = 'Rp ' . number_format($order->total_amount, 0, ',', '.');

            $paymentDetails = match ($paymentMethod) {
                'virtualaccount' => <<<TXT
            *🧾 Metode Pembayaran:* _Virtual Account_
            *🔖 Nomor Referensi:* _{$order->reference}_
            *🏦 Nomor VA :* _{$order->va_number}_
            *💰 Nominal :* _{$formattedAmount}_
            *⏰ Expired :* _{$expiredTime}_
            TXT,

                'ewallet' => <<<TXT
            *🧾 Metode Pembayaran:* _E-Wallet_
            *🔖 Nomor Referensi:* _{$order->reference}_
            *👛 E-Wallet :* _{$order->payment_name}_
            *💰 Nominal :* _{$formattedAmount}_
            *⏰ Expired :* _{$expiredTime}_
            TXT,

                'conveniencestore' => <<<TXT
            *🧾 Metode Pembayaran:* _Gerai (Alfamart/Alfamidi/Indomaret)_
            *🔖 Nomor Referensi:* _{$order->reference}_
            *🏪 Gerai :* _{$order->payment_name}_
            *🔢 Kode Bayar :* _{$order->va_number}_
            *💰 Nominal :* _{$formattedAmount}_
            *⏰ Expired :* _{$expiredTime}_
            TXT,

                default => <<<TXT
            *Metode Pembayaran:* _Tunggu konfirmasi_
            *🔖 Nomor Referensi:* _{$order->reference}_
            *💰 Nominal :* _{$formattedAmount}_
            *⏰ Expired :* _{$expiredTime}_
            TXT,
            };

            $replace = [
                '%name%' => $billing->customer->name,
                '%customer_id%' => $billing->customer->customer_id,
                '%billing_number%' => $billing->billing_number,
                '%paket%' => $billing->paket_name ?? 'Tidak tersedia informasi paket',
                // '%reference%' => $order->reference,
                // '%va_number%' => $order->va_number,
                // '%amount%' => $order->total_amount,
                // '%expired_at%' => date('d:m:Y H:i', $order->expired_time),
                '%admin%' => $billing->customer->user->name,
                '%customer_service%' => $whatsapp_number,
                '%payment_details%' => $paymentDetails,
            ];

            $messages = [
                'create_order' => 'create_order',
            ];

            if (!isset($messages[$action])) {
                LogHelper::logWarning(
                    null,
                    [
                        'billing_id' => $billing->id,
                        'action' => $action
                    ],
                    400, // Bad Request
                    "Aksi tidak valid: {$action} untuk billing ID: {$billing->id}"
                );
                return;
            }

            $message = $this->getDefaultMessage($messages[$action]);
            if (!$message) {
                LogHelper::logWarning(
                    null,
                    ['billing_id' => $billing->id, 'action' => $action],
                    404, // Not Found
                    "Template pesan untuk aksi '{$action}' tidak ditemukan untuk billing ID: {$billing->id}"
                );
                return;
            }

            $message = $this->replaceMessagePlaceholders($message, $replace);
            $this->sendWhatsappSingleJob($billing->customer_id, $billing->customer->name, $customer_contact, $gateway, $message, $action, $traceId, null);
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'billing_id' => $billing->id ?? null,
                    'action' => $action,
                ],
                500, // Internal Error
                "Terjadi kesalahan dalam order_create: " . $e->getMessage()
            );
        }
    }

    /**Payment Paket
     * Unpayment Paket
     *
     */
    public function payment_paket($billing, $receipt, $action)
    {
        $traceId = (string) Str::uuid();
        try {
            $gateway = $this->getGateway();
            $customer_contact = $billing->customer->contact;

            if (!$gateway || !$customer_contact) {
                LogHelper::logWarning(
                    null,
                    [
                        'billing_id' => $billing->id,
                        'gateway' => $gateway ? 'Available' : 'Not Available',
                        'customer_contact' => $customer_contact ? 'Available' : 'Not Available',
                    ],
                    400, // Bad Request
                    "Gateway atau kontak pelanggan tidak tersedia untuk billing ID: {$billing->id}."
                );
                return;
            }

            $userId = $billing->customer->user_id ?? 1;
            $whatsapp_number = $this->formatter->formatReceiver($this->getWhatsAppNumber($userId), null);

            $discount = 0;
            $price_after_discount = $billing->customer->paket->price;
            $member = 'Non Member / Tidak Ada';

            if ($action === 'paylater') {
                $billingOption = $billing->customer->billing_option;
                $billingPeriodMatch = $billing->customer->billing_period === $billing->billing_periode->name;

                if ($billingOption == 1 || ($billingOption == 2 && $billingPeriodMatch)) {
                    $discount = optional($billing->customer->member)->discount ?? 0;
                    $member = optional($billing->customer->member)->type ?? 'Non Member / Tidak Ada';
                }

                $price_after_discount -= $discount;
            } else {
                $discount = $receipt?->discount ?? 0;
                $price_after_discount = $receipt?->price_after_discount ?? $billing->paket_price;
                $member = $receipt?->member_type ?? 'Non Member / Tidak Ada';
            }


            $replace = [
                '%name%' => $billing->customer->name,
                '%address%' => $billing->customer->address,
                '%customer_id%' => $billing->customer->customer_id,
                '%billing_number%' => $billing->billing_number,
                '%paket%' => $receipt->paket_name ?? $billing->paket_name,
                '%periode%' => $billing->billing_periode->name,
                '%bill%' => 'Rp. ' . number_format($receipt->paket_price ?? $billing->paket_price, 2),
                '%discount%' => 'Rp. ' . number_format($discount, 2),
                '%total%' => 'Rp. ' . number_format($price_after_discount, 2),
                '%member%' => $member,
                '%payment_time%' => $receipt ? Carbon::parse($receipt->payment_time)
                    ->setTimezone('Asia/Jakarta')
                    ->translatedFormat('d F Y, H:i') . ' WIB'
                    : null,
                '%teller%' => $receipt->teller_name ?? $billing->teller_name,
                '%payment_methode%' => $receipt ? ($action == 'transfer'
                    ? "{$receipt->payment_methode} {$receipt->bank_name} - {$receipt->account_name} - {$receipt->account_number}"
                    : $receipt->payment_methode)
                    : 'Pay Later',
                '%paylater%' => $billing->pay_later ? Carbon::parse($billing->pay_later)->setTimezone('Asia/Jakarta')
                    ->translatedFormat('d F Y') : null,
                '%whatsapp_number%' => $whatsapp_number
            ];

            $messages = [
                'transfer'          => 'payment',
                'cash'              => 'payment',
                'paylater'          => 'paylater',
                'unpayment'         => 'unpayment',
                'payment_gateway'   => 'payment'
            ];

            if (!isset($messages[$action])) {
                LogHelper::logWarning(
                    null,
                    [
                        'billing_id' => $billing->id,
                        'action' => $action
                    ],
                    400, // Bad Request
                    "Aksi tidak valid: {$action} untuk billing ID: {$billing->id}"
                );
                return;
            }

            $message = $this->getDefaultMessage($messages[$action]);
            if (!$message) {
                LogHelper::logWarning(
                    null,
                    ['billing_id' => $billing->id, 'action' => $action],
                    404, // Not Found
                    "Template pesan untuk aksi '{$action}' tidak ditemukan untuk billing ID: {$billing->id}"
                );
                return;
            }

            $message = $this->replaceMessagePlaceholders($message, $replace);
            $this->sendWhatsappSingleJob($billing->customer_id, $billing->customer->name, $customer_contact, $gateway, $message, $action . '_payment', $traceId, null);
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'billing_id' => $billing->id ?? null,
                    'action' => $action,
                ],
                500, // Internal Error
                "Terjadi kesalahan dalam payment_paket: " . $e->getMessage()
            );
        }
    }

    public function bill_info($billing, $action, $traceId)
    {
        // Cek validitas billing dan nomor kontak
        if (!is_object($billing) || empty(trim($billing->customer_contact ?? ''))) {
            LogHelper::logError(null, [
                'billing_id' => $billing->id ?? null,
                'action' => $action,
            ], 400, "Billing atau contact customer tidak valid.");
            return null;
        }

        // Ambil informasi dasar
        $address = $billing->customer_address ?? 'Alamat tidak tersedia';
        $customer_unique_id = $billing->customer_unique_id ?? 'Customer ID tidak tersedia';

        // Normalisasi dan validasi kontak
        $contacts = [
            'customer' => [
                'raw' => $billing->customer_contact ?? null,
                'label' => 'customer',
                'check' => 'broadcast',
                'id' => $billing->customer_id ?? null,
                'name' => $billing->customer_name ?? null,
            ],
            'admin' => [
                'raw' => $billing->admin_contact ?? null,
                'label' => 'admin',
                'check' => null,
                'id' => $billing->admin_id ?? null,
                'name' => $billing->admin_name ?? null,
            ]
        ];

        foreach ($contacts as $type => $data) {
            $processed = $this->formatter->formatReceiver($data['raw'], $data['check']);
            if (!$processed) {
                LogHelper::logError(null, [
                    "{$data['label']}_id" => $data['id'],
                    "{$data['label']}_name" => $data['name'],
                    "contact_{$data['label']}" => $data['raw'],
                ], 400, "Nomor kontak {$data['label']} tidak valid.");
                return null;
            }
            ${"{$type}_contact"} = $processed;
            ${"{$type}_id"} = $data['id'];
            ${"{$type}_name"} = $data['name'];

            if ($type === 'admin') {
                $admin_id = $data['id'];
                $admin_contact = $data['raw'];
            }
        }

        // LogHelper::logInfo(
        //     null,
        //     [
        //         'address' => $address,
        //         'customer_unique_id' => $customer_unique_id,
        //         'admin_id' => $admin_id,
        //         'admin_contact' => $admin_contact
        //     ],
        //     200,
        //     "admin id",
        //     $traceId
        // );

        // Ambil informasi rekening admin
        $accountBank = $this->getBankInfo($admin_id ?? 1);

        // Ambil rincian paket dan tagihan
        $pakets = $this->getBillingPackages($customer_id);
        if ($pakets->isEmpty()) {
            LogHelper::logError(null, [
                'customer_id' => $customer_id,
                'customer_name' => $customer_name,
                'contact_customer' => $contact,
            ], 404, "Paket atau tagihan tidak ditemukan.");
            return null;
        }

        $totalAmount = $pakets->sum('paket_price');
        $remainingDays = Carbon::today()->diffInDays(Carbon::parse($pakets->first()->due_date), false);

        // Format rincian paket
        $paketsText = $pakets->map(fn($paket) => $this->formatPaketInfo($paket))->implode("\n---\n");

        // Template message
        $replace = [
            '%name%' => $customer_name,
            '%address%' => $address,
            '%customer_id%' => $customer_unique_id,
            '%billing_number%' => $billing->billing_number ?? 'Nomor tidak tersedia',
            '%account_bank%' => $accountBank,
            '%day%' => $remainingDays,
            '%pakets%' => $paketsText,
            '%whatsapp_number%' => $admin_contact,
            '%total-bill%' => 'Rp. ' . number_format($totalAmount, 2),
        ];

        $messageTemplate = $this->getMessageTemplate($action, $pakets, $admin_name);
        return $this->replaceMessagePlaceholders($messageTemplate, $replace);
    }

    //================================Manual send by admin=======================================
    public function broadcast_message($message) {}

    public function test_gateway($gateway, $receive_number, $sender_number)
    {
        $traceId = (string) Str::uuid();
        try {
            LogHelper::logInfo(
                null,
                [
                    'sender_number' => $sender_number,
                    'receive_number' => $receive_number,
                    'gateway_id' => $gateway->id ?? null
                ],
                100, // Continue
                "Starting test_gateway process"
            );

            // Cari instance WhatsappNumber berdasarkan nomor
            $whatsapp_number = WhatsappNumber::where('number', $sender_number)->first();

            if (!$whatsapp_number) {
                LogHelper::logError(
                    null,
                    [
                        'sender_number' => $sender_number ?? null,
                        'whatsapp_number_id' => $whatsapp_number->id ?? null
                    ],
                    404,
                    "WhatsApp number not found"
                );
                return redirect()->back()->with('error', 'WhatsApp number or gateway not found.');
            }

            LogHelper::logInfo(
                null,
                ['whatsapp_number_id' => $whatsapp_number->id],
                101,
                "Found WhatsApp number"
            );

            if (!$receive_number) {
                LogHelper::logWarning(
                    null,
                    ['sender_number' => $sender_number],
                    400,
                    "Receive number is missing"
                );
                return redirect()->back()->with('error', 'Receive number is required.');
            }

            LogHelper::logInfo(
                null,
                [
                    'gateway_id' => $gateway->id ?? null,
                    'receive_number' => $receive_number
                ],
                102,
                "Attempting to send test message"
            );

            try {
                $message = 'Congratulations!! Test message from Customer Management WA Gateway';
                $full_name = "Dummy Customer";
                $this->sendWhatsappSingleJob(null, $full_name, $receive_number, $gateway, $message, 'test_message', $traceId, 'yes');

                LogHelper::logInfo(
                    null,
                    [
                        'receive_number' => $receive_number,
                        'gateway_id' => $gateway->id
                    ],
                    200,
                    "Test message dispatched successfully"
                );
                return redirect()->back()->with('success', 'Test message sent successfully.');
            } catch (\Exception $e) {
                LogHelper::logError(
                    null,
                    ['receive_number' => $receive_number],
                    500,
                    "Failed to send WhatsApp message: {$e->getMessage()}"
                );
                return redirect()->back()->with('error', 'Failed to send test message.');
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'sender_number' => $sender_number,
                    'receive_number' => $receive_number
                ],
                500,
                "Unexpected error in test_gateway: {$e->getMessage()}"
            );
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }
}
