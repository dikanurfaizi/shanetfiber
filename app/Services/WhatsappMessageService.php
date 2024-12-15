<?php

namespace App\Services;

use App\Jobs\SendWhatsappSingleJob;
use Illuminate\Support\Carbon;

use App\Models\WhatsappGateway\WhatsappNumber;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Models\WhatsappGateway\WhatsappMessage;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\Receipt;
use Illuminate\Support\Facades\Log;
use App\Models\Billing\Billing;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;

class WhatsappMessageService
{
    /**
     * Chesk status wa gateway
     * return false = enable, return true = disable
     */
    public function getGateway()
    {
        $gateway = WhatsappGateway::where('slug', 'gateway')
            ->where('disabled', false)
            ->first();
        if ($gateway && $gateway->whatsapp_number->disabled == false) {
            return $gateway;
        }
    }

    public function getMessage($incoming_message)
    {
        $message = WhatsappMessage::where('incoming_message', $incoming_message)
            ->where('disabled', false)
            ->where('default', false)
            ->first();
        // ->first();
        return $message;
    }

    public function getAccountBank()
    {
        $account_banks_text = '';
        $account_banks = Bank::where('disabled', false)
            ->get();

        if (count($account_banks)) {
            $i = 0;
            foreach ($account_banks as $account_bank) {
                $i++;
                $account_banks_text .= $i . '. ' . $account_bank->bank . ' - ' . $account_bank->account_number . ' - ' . $account_bank->account_name . '%0a';
            }
        }
        return $account_banks_text;
    }

    public function getDefaultMessage($slug)
    {
        $message = WhatsappMessage::where('slug', $slug)
            ->where('disabled', false)
            ->where('default', true)
            ->value('reply_message');
        return $message;
    }

    public function getCustomer($customer_number)
    {
        return Customer::where('contact', $customer_number)
            ->first();
    }

    public function manipulateContact($billing)
    {
        // Ambil nomor kontak customer
        $customer_contact = $billing->customer->contact;

        if (is_null($customer_contact) || trim($customer_contact) === '') {
            Log::info("Contact number is null or empty for billing ID: {$billing->id}");
            return null; // Tidak perlu diproses jika null
        }

        // Periksa apakah $customer_contact adalah angka
        if (!is_numeric($customer_contact)) {
            // Jika bukan angka, kembalikan error
            return redirect()->back()->withErrors(['contact' => 'Invalid contact number. Only numbers are allowed.']);
        }

        // Jika nomor dimulai dengan 0, ubah 0 menjadi 62
        if (substr($customer_contact, 0, 1) == '0') {
            $customer_contact = '62' . substr($customer_contact, 1);
        }

        // Jika nomor sudah diawali dengan 62, biarkan saja
        if (substr($customer_contact, 0, 2) == '62') {
            // Tidak ada perubahan
        }

        // Kembalikan nomor yang sudah dimanipulasi (jika perlu disimpan atau diproses lebih lanjut)
        return $customer_contact;
    }

    public function create_billing($billing)
    {
        try {
            // Ambil gateway dan kontak pelanggan
            $gateway = $this->getGateway();
            $customer_contact = $this->manipulateContact($billing);

            if ($gateway && $customer_contact) {
                // Ambil informasi bank
                $banks = Bank::where('disabled', 'false')
                    ->select('name', 'bank', 'account_number')
                    ->get();
                $accountBank = $banks->map(function ($bank) {
                    return $bank->bank . ' - ' . $bank->account_number . ' (a.n ' . $bank->name . ') ';
                })->implode("\n") ?? 'Tidak ada informasi bank';

                // Data yang akan di-replace
                $full_name = $billing->customer->name;
                $replace = [
                    '%name%' => $full_name,
                    '%address%' => $billing->customer->address,
                    '%customer_id%' => $billing->customer->customer_id,
                    '%paket-name%' => $billing->paket_name,
                    '%bill%' => 'Rp.' . number_format($billing->paket_price, 2),
                    '%account-bank%' => $accountBank,
                    '%periode%' => $billing->billing_periode->name,
                    '%deadline%' => $billing->due_date,
                ];

                // Ambil template pesan
                $message = $this->getDefaultMessage('create_new_bill');
                if ($message) {
                    // Replace placeholder dengan data sebenarnya
                    $message = str_replace(array_keys($replace), $replace, $message);

                    // Kirim ke job untuk dispatch
                    dispatch(new SendWhatsappSingleJob($full_name, $customer_contact, $gateway, $message))
                        ->onQueue('default');
                } else {
                    Log::warning("Template pesan untuk 'create_new_bill' tidak ditemukan.");
                }
            } else {
                Log::info("Gateway atau kontak pelanggan tidak tersedia untuk billing ID: {$billing->id}.");
            }
        } catch (RequestException $e) {
            // Tangkap dan log error spesifik terkait permintaan HTTP
            Log::error("HTTP Request Exception: " . $e->getMessage(), [
                'billing_id' => $billing->id,
                'customer_id' => $billing->customer->customer_id ?? null,
            ]);
        } catch (\Exception $e) {
            // Tangkap error lainnya
            Log::error("Exception occurred while creating billing: " . $e->getMessage(), [
                'billing_id' => $billing->id,
                'customer_id' => $billing->customer->customer_id ?? null,
            ]);
        }
    }

    /**Payment Paket
     * Unpayment Paket
     *
     */
    public function payment_paket($billing, $action)
    {
        try {
            // Ambil gateway dan kontak pelanggan
            $gateway = $this->getGateway();
            $customer_contact = $this->manipulateContact($billing);

            if ($gateway && $customer_contact) {
                $full_name = $billing->customer->name;
                $billing_periode = $billing->billing_periode->name;
                $replace = [
                    '%name%' => $full_name,
                    '%address%' => $billing->customer->address,
                    '%customer_id%' => $billing->customer->customer_id,
                    '%billing_number%' => $billing->billing_number,
                ];

                // Penentuan pesan berdasarkan action
                $message = null;

                if ($action == 'transfer' || $action == 'cash') {
                    $message = $this->getDefaultMessage('payment');
                    $receipt = Receipt::where('id', $billing->receipt_id)->first();

                    if ($receipt) {
                        $replace['%paket%'] = $receipt->paket_name;
                        $replace['%periode%'] = $billing_periode;
                        $replace['%bill%'] = 'Rp.' . number_format($receipt->paket_price, 2);
                        $replace['%payment_time%'] = Carbon::parse($receipt->payment_time)->format('d F Y H:i:s');
                        $replace['%teller%'] = $receipt->teller_name;

                        if ($action == 'transfer') {
                            $replace['%payment_methode%'] = $receipt->payment_methode . ' ' . $receipt->bank_name . ' - ' . $receipt->account_name . ' - ' . $receipt->account_number;
                        } else {
                            $replace['%payment_methode%'] = $receipt->payment_methode;
                        }
                    } else {
                        Log::warning("Receipt not found for billing ID: {$billing->id}");
                    }
                } elseif ($action == 'paylater') {
                    $message = $this->getDefaultMessage('paylater');
                    $replace['%paket%'] = $billing->paket_name;
                    $replace['%periode%'] = $billing_periode . ' - ' . Carbon::parse($billing_periode)->subDay()->addMonthsNoOverflow(1)->format('d F Y');
                    $replace['%bill%'] = 'Rp.' . number_format($billing->paket_price, 2);
                    $replace['%teller%'] = $billing->teller_name;
                    $replace['%payment_methode%'] = 'Pay Later';
                    $replace['%paylater%'] = Carbon::parse($billing->pay_later)->format('d F Y');
                } elseif ($action == 'unpayment') {
                    $message = $this->getDefaultMessage('unpayment');
                    $replace['%bill%'] = 'Rp.' . number_format($billing->paket_price, 2);
                }

                // Kirim pesan jika template tersedia
                if ($message) {
                    try {
                        $message = str_replace(array_keys($replace), $replace, $message);
                        dispatch(new SendWhatsappSingleJob($full_name, $customer_contact, $gateway, $message))
                            ->onQueue('default');
                    } catch (RequestException $e) {
                        Log::error("HTTP Request Exception while sending WhatsApp message: " . $e->getMessage(), [
                            'billing_id' => $billing->id,
                            'customer_contact' => $customer_contact,
                            'gateway' => $gateway->id ?? null,
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Exception occurred while dispatching WhatsApp message: " . $e->getMessage(), [
                            'billing_id' => $billing->id,
                            'customer_contact' => $customer_contact,
                            'gateway' => $gateway->id ?? null,
                        ]);
                    }
                } else {
                    Log::warning("Message template for action '{$action}' not found for billing ID: {$billing->id}");
                }
            } else {
                Log::info("Gateway or customer contact not available for billing ID: {$billing->id}");
            }
        } catch (\Exception $e) {
            Log::error("Exception occurred in payment_paket method: " . $e->getMessage(), [
                'billing_id' => $billing->id ?? null,
                'action' => $action,
            ]);
        }
    }

    public function bill_info($billing, $action)
    {
        try {
            // Ambil gateway dan informasi customer
            $gateway = $this->getGateway();
            $customer_billing = Billing::with(['customer', 'billing_periode'])
                ->where('customer_id', $billing->customer_id)
                ->first();
            $customer_contact = $this->manipulateContact($customer_billing);

            if ($gateway && $customer_contact) {
                // Default values
                $full_name = $billing->customer_name ?? 'Pelanggan';
                $address = $billing->customer_address ?? 'Alamat tidak tersedia';
                $customer_id = $billing->customer_id ?? 'ID tidak tersedia';

                // Ambil informasi bank
                $banks = Bank::where('disabled', 'false')
                    ->select('name', 'bank', 'account_number')
                    ->get();
                $accountBank = $banks->map(function ($bank) {
                    return $bank->bank . ' - ' . $bank->account_number . ' (a.n ' . $bank->name . ')';
                })->implode("\n") ?? 'Tidak ada informasi bank';

                // Ambil rincian paket
                $pakets = Billing::with('billing_periode')
                    ->select('paket_name', 'billing_periode_id', 'paket_price', 'status', 'due_date', 'pay_later')
                    ->where('customer_id', $customer_id)
                    ->whereIn('status', ['BL', 'PL'])
                    ->orderByRaw("CASE WHEN status = 'BL' THEN due_date ELSE pay_later END DESC")
                    ->get();

                if ($pakets->isEmpty()) {
                    Log::info("No bills found for customer ID: $customer_id");
                    return;
                }

                $today = Carbon::today();
                $specificTarget = $pakets->first();
                $targetDate = $specificTarget->status == 'BL' ? $specificTarget->due_date : $specificTarget->pay_later;
                $dueDate = Carbon::parse($targetDate);
                $remainingDays = $today->diffInDays($dueDate, false);

                // Data replace umum
                $replace = [
                    '%name%' => $full_name,
                    '%address%' => $address,
                    '%customer_id%' => $customer_id,
                    '%billing_number%' => $billing->billing_number ?? 'Nomor tidak tersedia',
                    '%account-bank%' => $accountBank,
                    '%day%' => $remainingDays,
                ];

                // Format rincian paket
                $paketsText = '';
                $totalAmount = 0;
                foreach ($pakets as $paket) {
                    $billingPeriodeName = $paket->billing_periode->name ?? 'Tidak ada periode';
                    $paketsText .= sprintf(
                        "*Paket: %s*\n- Periode: %s\n- Harga: Rp. %s\n",
                        $paket->paket_name,
                        $billingPeriodeName,
                        number_format($paket->paket_price, 2)
                    );
                    $totalAmount += $paket->paket_price;
                }

                $replace['%pakets%'] = $paketsText;
                $replace['%total-bill%'] = 'Rp.' . number_format($totalAmount, 2);

                // Penanganan berdasarkan aksi
                switch ($action) {
                    case 'warning_bill':
                        $message = $this->getDefaultMessage('past_due_warning');
                        $replace['%count-bill%'] = $pakets->count();
                        break;

                    case 'deadline_bill':
                        $message = $this->getDefaultMessage('deadline_bill');
                        $replace['%count-bill%'] = $pakets->count();
                        break;

                    case 'reminder_bill':
                        $message = $this->getDefaultMessage('reminder_bill');
                        $replace['%count-bill%'] = $pakets->count();
                        break;

                    default:
                        Log::error("Invalid action provided: $action");
                        return;
                }

                // Kirim pesan jika ada
                if ($message) {
                    try {
                        $message = str_replace(array_keys($replace), $replace, $message);
                        dispatch(new SendWhatsappSingleJob($full_name, $customer_contact, $gateway, $message))->onQueue('default');
                    } catch (RequestException $e) {
                        Log::error("Failed to send WhatsApp message for customer ID: $customer_id, error: {$e->getMessage()}");
                    } catch (\Exception $e) {
                        Log::error("Unexpected error occurred while dispatching message: {$e->getMessage()}", [
                            'customer_id' => $customer_id,
                            'action' => $action,
                        ]);
                    }
                } else {
                    Log::warning("Message template for action '{$action}' not found.");
                }
            } else {
                Log::error("Gateway or contact missing for customer ID: {$billing->customer_id}");
            }
        } catch (\Exception $e) {
            Log::error("Error occurred in bill_info method: {$e->getMessage()}", [
                'billing_id' => $billing->id ?? null,
                'action' => $action,
            ]);
        }
    }

    //================================Manual send by admin=======================================
    public function broadcast_message($message) {}

    public function test_gateway($receive_number, $sender_number)
    {
        try {
            // Ambil gateway
            $gateway = $this->getGateway();

            // Cari instance WhatsappNumber berdasarkan nomor
            $whatsapp_number = WhatsappNumber::where('number', $sender_number)->first();
            if (!$whatsapp_number) {
                Log::error("WhatsApp number not found for sender number: {$sender_number}");
                return redirect()->back()->with('error', 'WhatsApp number not found.');
            }

            // Pastikan instance gateway ditemukan
            if (!$gateway) {
                Log::error("Gateway not found for WhatsApp number ID: {$whatsapp_number->id}");
                return redirect()->back()->with('error', 'Gateway not found.');
            }

            $message = 'Congratulations!! Test message from Customer Management WA Gateway';

            if ($receive_number) {
                try {
                    // Dispatch job dengan instance WhatsappGateway
                    $full_name = "Dummy Customer";
                    dispatch(new SendWhatsappSingleJob($full_name, $receive_number, $gateway, $message))->onQueue('default');
                    Log::info("Test message dispatched successfully to {$receive_number} via gateway {$gateway->id}");
                    return redirect()->back()->with('success', 'Test message sent successfully.');
                } catch (\Exception $e) {
                    Log::error("Failed to send WhatsApp message to {$receive_number}: {$e->getMessage()}");
                    return redirect()->back()->with('error', 'Failed to send test message.');
                }
            } else {
                Log::warning("Receive number is missing.");
                return redirect()->back()->with('error', 'Receive number is required.');
            }
        } catch (\Exception $e) {
            Log::error("An unexpected error occurred in test_gateway: {$e->getMessage()}", [
                'sender_number' => $sender_number,
                'receive_number' => $receive_number,
            ]);
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }
}
