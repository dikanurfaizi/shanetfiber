<?php

namespace App\Services;

use App\Jobs\SendWhatsappSingleJob;
use Illuminate\Support\Carbon;

use App\Models\WhatsappGateway\WhatsappNumber;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Models\WhatsappGateway\WhatsappMessage;
use App\Models\User;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\Billing\Billing;
use App\Helpers\LogHelper;

class WhatsappMessageService
{
    /**
     * Chesk status wa gateway
     * return false = enable, return true = disable
     */
    // private function sendWhatsappSingleJob($customerName, $receiver, $gateway, $message)
    // {
    //     $delay = now()->addMinutes(rand(2, 3));
    //     dispatch(new SendWhatsappSingleJob($customerName, $receiver, $gateway, $message))->delay($delay);
    // }
    private function sendWhatsappSingleJob($customerName, $receiver, $gateway, $message)
    {
        dispatch(new SendWhatsappSingleJob($customerName, $receiver, $gateway, $message));
    }

    public function getGateway()
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

    public function getAccountBank()
    {
        try {
            $account_banks_text = '';
            $account_banks = Bank::where('disabled', false)->get();

            if ($account_banks->isNotEmpty()) {
                $i = 0;
                foreach ($account_banks as $account_bank) {
                    $i++;
                    $account_banks_text .= $i . '. ' . $account_bank->bank . ' - ' . $account_bank->account_number . ' - ' . $account_bank->account_name . '%0a';
                }
            }
            return $account_banks_text;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                null,
                500,
                "Error retrieving account bank information: " . $e->getMessage()
            );
            return "Gagal mengambil informasi rekening bank.";
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

    public function manipulateContactBillInfo($contact, $action = null)
    {
        try {
            // Hapus karakter non-digit, kecuali tanda "+" di awal
            $contact = preg_replace('/[^\d+]/', '', $contact);

            // Jika nomor diawali dengan "+62", ubah menjadi "62"
            if (str_starts_with($contact, '+62')) {
                $contact = substr($contact, 1);
            }

            if ($action === 'broadcast') {
                // Jika nomor dimulai dengan "08", ubah ke format "62"
                if (str_starts_with($contact, '08')) {
                    $contact = '62' . substr($contact, 1);
                }
            } else {
                // Jika nomor diawali dengan "628", langsung ubah menjadi "08"
                $contact = preg_replace('/^628/', '08', $contact);

                // Tambahkan tanda "-" sesuai pola nomor HP
                $contact = preg_replace('/^(\d{4})(\d{4})(\d+)$/', '$1-$2-$3', $contact);
            }

            return $contact;
        } catch (\Exception $e) {
            LogHelper::logWarning(
                null,
                null,
                500, // Bad Request
                "Error manipulating contact bill info for contact: $contact | Action: $action | " . $e->getMessage()
            );
            return null;
        }
    }

    /**
     * Ambil informasi bank dalam format string
     */
    private function getBankInfo()
    {
        try {
            $bankInfo = Bank::where('disabled', 'false')
                ->select('name', 'bank', 'account_number')
                ->get()
                ->map(fn($bank) => "{$bank->bank} - {$bank->account_number} (a.n {$bank->name})")
                ->implode("\n");

            if (!$bankInfo) {
                LogHelper::logWarning(
                    null,
                    null,
                    404, // Bad Request
                    "No bank information available"
                );
                return 'Tidak ada informasi bank';
            }

            return $bankInfo;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                null,
                500, // Bad Request
                "Failed to retrieve bank information. Error: {$e->getMessage()}"
            );
            return 'Terjadi kesalahan saat mengambil informasi bank';
        }
    }

    /**
     * Ambil rincian paket pelanggan
     */
    private function getBillingPackages($cust_id)
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
    private function formatPaketInfo($paket)
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
    private function getMessageTemplate($action, $pakets, $admin)
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
    private function getWhatsAppNumber()
    {
        try {
            $number = WhatsappNumber::where('disabled', 0)->first()->number ?? null;

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
    private function replaceMessagePlaceholders($message, array $replace)
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

    public function activation($customers, $action)
    {
        $admin_number = null;
        try {
            $gateway = $this->getGateway();
            $customer_contact = $this->manipulateContactBillInfo($customers->contact, 'broadcast');

            if (!$gateway || !$customer_contact) {
                LogHelper::logWarning(
                    null,
                    [
                        'customer_id' => $customers->id,
                        'gateway' => $gateway ? 'Available' : 'Not Available',
                        'customer_contact' => $customer_contact ? 'Available' : 'Not Available',
                    ],
                    400, // Bad Request
                    "Gateway atau kontak pelanggan tidak tersedia untuk billing ID: {$customers->id}."
                );
                return;
            }

            $admin = User::Where('id', $customers->user_id)->first();
            $admin_number = '-';
            if (!empty($admin->contact)) {
                $admin_number = $this->manipulateContactBillInfo($admin->contact, null);
            }

            $replace = [
                '%name%' => $customers->name,
                '%address%' => $customers->address,
                '%email%' => $customers->email ?? '-',
                '%customer_service%' => $admin_number,
                '%customer_id%' => $customers->customer_id,
                '%admin%' => $admin->name,
                '%paket%' => $customers->paket->name,
                '%bill%' => 'Rp. ' . number_format($customers->paket->price, 2, ',', '.'),
            ];

            $message = $this->getDefaultMessage($action);
            if (!$message) {
                LogHelper::logWarning(
                    null,
                    [
                        'id' => $customers->id,
                        'name' => $customers->name,
                        'address' => $customers->address,
                        'email' => $customers->email ?? '-',
                        'customer_service' => $admin_number,
                        'customer_id' => $customers->customer_id,
                        'action' => $action,
                    ],
                    404, // Not Found
                    "Template pesan untuk aksi '{$action}' tidak ditemukan untuk customer ID: {$customers->id}"
                );
                return;
            }

            $message = $this->replaceMessagePlaceholders($message, $replace);
            $this->sendWhatsappSingleJob($customers->name, $customer_contact, $gateway, $message);
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'id' => $customers->id,
                    'name' => $customers->name,
                    'address' => $customers->address,
                    'email' => $customers->email ?? '-',
                    'customer_service' => $admin_number,
                    'customer_id' => $customers->customer_id,
                    'action' => $action,
                ],
                500, // Internal Error
                "Terjadi kesalahan dalam {$action}: " . $e->getMessage()
            );
        }
    }

    /**Payment Paket
     * Unpayment Paket
     *
     */
    public function payment_paket($billing, $receipt, $action)
    {
        try {
            $gateway = $this->getGateway();
            $customer_contact = $this->manipulateContactBillInfo($billing->customer->contact, 'broadcast');

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

            $whatsapp_number = $this->manipulateContactBillInfo($this->getWhatsAppNumber(), null);

            $discount = 0;
            $price_after_discount = $billing->customer->paket->price;
            $member = 'Non Member / Tidak Ada';

            if ($action === 'paylater') {
                $billingOption = $billing->customer->billing_option;
                $billingPeriodMatch = $billing->customer->billing_period === $billing->billing_periode->name;

                if ($billingOption == 1 || ($billingOption == 2 && $billingPeriodMatch)) {
                    $discount = $billing->customer->member->discount;
                    $member = $billing->customer->member->type;
                }

                $price_after_discount -= $discount;
            } else {
                $discount = $receipt->discount;
                $price_after_discount = $receipt->price_after_discount;
                $member = $receipt->member_type;
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
                'transfer'  => 'payment',
                'cash'      => 'payment',
                'paylater'  => 'paylater',
                'unpayment' => 'unpayment'
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
            $this->sendWhatsappSingleJob($billing->customer->name, $customer_contact, $gateway, $message);
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

    public function bill_info($billing, $action)
    {
        try {
            if (!is_object($billing)) {
                LogHelper::logError(
                    null,
                    [
                        'billing_id' => $billing->id ?? null,
                        'action' => $action,
                    ],
                    400, // Bad Request
                    "Billing is not an object"
                );
                return;
            }

            $contact = $billing->customer_contact;
            if (empty(trim($contact))) {
                LogHelper::logError(
                    null,
                    [
                        'billing_id' => $billing->id ?? null,
                        'action' => $action,
                    ],
                    400, // Bad Request
                    "Contact number is null or empty"
                );
                return;
            }

            $gateway = $this->getGateway();
            $customer_contact = $this->manipulateContactBillInfo($contact, 'broadcast');

            if (!$customer_contact) {
                LogHelper::logError(
                    null,
                    [
                        'customer_id' => $billing->customer_id,
                        'contact_customer' => $customer_contact,
                        'gateway' => $gateway
                    ],
                    400, // Bad Request
                    "Gateway or contact missing"
                );
                return;
            }

            // Ambil informasi customer
            $full_name = $billing->customer_name ?? 'Pelanggan';
            $address = $billing->customer_address ?? 'Alamat tidak tersedia';
            $cust_id = $billing->customer_id ?? 'ID tidak tersedia';
            $customer_id = $billing->customer_unique_id ?? 'Customer ID tidak tersedia';
            $admin = $billing->admin_name ?? 'Belum terlink ke admin';

            // Ambil informasi bank
            $accountBank = $this->getBankInfo();

            // Ambil rincian paket
            $pakets = $this->getBillingPackages($cust_id);
            if ($pakets->isEmpty()) {
                LogHelper::logError(
                    null,
                    [
                        'cust_id' => $cust_id,
                        'customer_id' => $billing->customer_id,
                        'contact_customer' => $customer_contact,
                        'gateway' => $gateway
                    ],
                    404, // Not Found
                    "No bills found"
                );
                return;
            }

            $whatsapp_number = $this->manipulateContactBillInfo($this->getWhatsAppNumber(), null);

            // Hitung jatuh tempo
            $today = Carbon::today();
            $specificTarget = $pakets->first();
            $targetDate = $specificTarget->status == 'BL' ? $specificTarget->due_date : $specificTarget->pay_later;
            $remainingDays = $today->diffInDays(Carbon::parse($targetDate), false);

            // Format rincian paket
            $paketsText = $pakets->map(fn($paket) => $this->formatPaketInfo($paket))->implode("\n---\n");
            $totalAmount = $pakets->sum('paket_price');

            // Data replace umum
            $replace = [
                '%name%' => $full_name,
                '%address%' => $address,
                '%customer_id%' => $customer_id,
                '%billing_number%' => $billing->billing_number ?? 'Nomor tidak tersedia',
                '%account-bank%' => $accountBank,
                '%day%' => $remainingDays,
                '%pakets%' => $paketsText,
                '%whatsapp_number%' => $whatsapp_number,
                '%total-bill%' => 'Rp. ' . number_format($totalAmount, 2),
            ];

            // Tentukan template pesan
            $message = $this->getMessageTemplate($action, $pakets, $admin);
            if (!$message) {
                LogHelper::logWarning(
                    null,
                    [
                        'action' => $action,
                        'paket' => $pakets,
                        'admin' => $admin
                    ],
                    404, // Not Found
                    "Message template not found"
                );
                return;
            }

            // Kirim pesan via WhatsApp
            try {
                $this->sendWhatsappSingleJob($full_name, $customer_contact, $gateway, str_replace(array_keys($replace), $replace, $message));

                LogHelper::logInfo(
                    null,
                    [
                        'customer_id' => $cust_id,
                        'billing_customer_id' => $customer_id,
                        'customer_name' => $full_name,
                        'customer_contact' => $customer_contact,
                        'address' => $address,
                        'message_type' => $action,
                        'admin' => $admin
                    ],
                    200, // OK
                    "Successfully processed message on message service, currently in delivery queue"
                );
            } catch (\Exception $e) {
                LogHelper::logError(
                    null,
                    [
                        'cust_id' => $cust_id ?? null,
                        'customer_id' => $customer_id ?? null,
                        'billing_id' => $billing->id ?? null,
                        'action' => $action ?? null,
                        'error' => $e->getMessage(),
                    ],
                    500, // Internal Error
                    "Failed to processed message on message service"
                );
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'cust_id' => $cust_id ?? null,
                    'customer_id' => $customer_id ?? null,
                    'billing_id' => $billing->id ?? null,
                    'action' => $action ?? null,
                    'error' => $e->getMessage(),
                ],
                500, // Internal Error
                "Error in bill_info method"
            );
        }
    }

    //================================Manual send by admin=======================================
    public function broadcast_message($message) {}

    public function test_gateway($gateway, $receive_number, $sender_number)
    {
        try {
            // Cari instance WhatsappNumber berdasarkan nomor
            $whatsapp_number = WhatsappNumber::where('number', $sender_number)->first();

            if (!$whatsapp_number) {
                LogHelper::logError(
                    null,
                    [
                        'sender_number' => $sender_number ?? null,
                        'whatsapp_number_id' => $whatsapp_number->id ?? null
                    ],
                    404, // Not Found
                    "WhatsApp number or gateway not found"
                );
                return redirect()->back()->with('error', 'WhatsApp number or gateway not found.');
            }

            if (!$receive_number) {
                LogHelper::logWarning(
                    null,
                    ['sender_number' => $sender_number],
                    400, // Bad Request
                    "Receive number is missing"
                );
                return redirect()->back()->with('error', 'Receive number is required.');
            }

            // Kirim pesan
            try {
                $message = 'Congratulations!! Test message from Customer Management WA Gateway';
                $full_name = "Dummy Customer";
                $this->sendWhatsappSingleJob($full_name, $receive_number, $gateway, $message);

                LogHelper::logInfo(
                    null,
                    [
                        'receive_number' => $receive_number,
                        'gateway_id' => $gateway->id
                    ],
                    200, // Success
                    "Test message dispatched successfully"
                );
                return redirect()->back()->with('success', 'Test message sent successfully.');
            } catch (\Exception $e) {
                LogHelper::logError(
                    null,
                    ['receive_number' => $receive_number],
                    500, // Internal Server Error
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
                500, // Internal Server Error
                "Unexpected error in test_gateway: {$e->getMessage()}"
            );
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }
}
