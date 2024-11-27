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


    /**Payment Paket
     * Unpayment Paket
     *
     */
    public function payment_paket($billing, $action)
    {
        $gateway = $this->getGateway();
        //$customer_contact = $billing->customer->contact;
        $customer_contact = $this->manipulateContact($billing);
        if ($gateway && $customer_contact) {
            $full_name = $billing->customer->name;
            $replace = [
                '%name%' =>  $full_name,
                '%address%' => $billing->customer->address,
                '%customer_id%' => $billing->customer->customer_id,
                '%billing_number%' =>  $billing->billing_number,
            ];
            $billing_periode = Carbon::parse($billing->customer->activation_date)->format('d') . ' ' . $billing->billing_periode->name;

            if ($action == 'transfer' || $action == 'cash') {
                $message = $this->getDefaultMessage('payment');
                $receipt = Receipt::where('id', $billing->receipt_id)->first();
                if ($receipt) {
                    $replace['%paket%'] = $receipt->paket_name;
                    $replace['%periode%'] = $billing_periode . ' - ' . Carbon::parse($billing_periode)->subDay()->addMonthsNoOverflow(1)->format('d F Y');
                    $replace['%bill%'] = 'Rp.' . number_format($receipt->paket_price, 2);
                    $replace['%payment_time%'] = Carbon::parse($receipt->payment_time)->format('d F Y H:i:s');
                    $replace['%teller%'] =  $receipt->teller_name;
                    if ($action == 'transfer') {
                        $replace['%payment_methode%'] = $receipt->payment_methode . ' ' . $receipt->bank_name . ' - ' . $receipt->account_name . ' - ' . $receipt->account_number;
                    } else {
                        $replace['%payment_methode%'] = $receipt->payment_methode;
                    }
                }
            } else if ($action == 'paylater') {
                $message = $this->getDefaultMessage('paylater');
                $replace['%paket%'] = $billing->paket_name;
                $replace['%periode%'] = $billing_periode . ' - ' . Carbon::parse($billing_periode)->subDay()->addMonthsNoOverflow(1)->format('d F Y');
                $replace['%bill%'] = 'Rp.' . number_format($billing->paket_price, 2);
                $replace['%teller%'] =  $billing->teller_name;
                $replace['%payment_methode%'] = 'Pay Later';
                $replace['%paylater%'] = Carbon::parse($billing->pay_later)->format('d F Y');
            } else if ($action == 'unpayment') {
                $replace['%bill%'] = 'Rp.' . number_format($billing->paket_price, 2);
                $message = $this->getDefaultMessage('unpayment');
            }
            if ($message) {
                //$host = $gateway->url_callback;
                //$certfile = $gateway->cert_file;
                //$client = new Client();

                define("read_timeout", \GuzzleHttp\RequestOptions::READ_TIMEOUT);
                try {
                    //$client = new Client();
                    //$client->request('GET', $host, ['verify' => $certfile]);
                    $message = str_replace(array_keys($replace), $replace, $message);
                    //dispatch(new SendWhatsappJob($customer_contact, $gateway->whatsapp_number->number, $message))->onQueue('default');
                    dispatch(new SendWhatsappSingleJob($customer_contact, $gateway, $message))->onQueue('default');
                } catch (RequestException $e) {
                    //Access the message or other type of errors and react to them
                    return;
                }
            }
        }
    }

    public function bill_info($billing, $action)
    {
        // Get gateway and customer contact information
        $gateway = $this->getGateway();
        $customer_billing = Billing::with(['customer', 'billing_periode'])->where('customer_name', $billing->customer_name)->first();
        $customer_contact = $this->manipulateContact($customer_billing);

        if ($gateway && $customer_contact) {
            $full_name = $customer_billing->customer->name ?? 'Pelanggan';
            $customer_name = $customer_billing->customer_name ?? 'Pelanggan';
            $address = $customer_billing->customer->address ?? 'Alamat tidak tersedia';
            $customer_id = $customer_billing->customer->customer_id ?? 'ID tidak tersedia';
            $paket_name = $customer_billing->paket_name ?? 'Paket tidak tersedia';
            $day = $gateway ? $gateway->remaining_day : 0;
            $periode = $customer_billing->billing_periode->name ?? 'Tidak ada periode';
            //$billing_periode = Carbon::parse($customer_billing->customer->activation_date)->format('d') . ' ' . $billing->billing_periode->name;

            // Ambil informasi bank
            $banks = Bank::where('disabled', 'false')
                ->select('name', 'bank', 'account_number')
                ->get();
            $accountBank = $banks->map(function ($bank) {
                return $bank->bank . ' - ' . $bank->account_number . ' (a.n ' . $bank->name . ') ';
            })->implode("\n") ?? 'Tidak ada informasi bank';

            // Data replace umum
            $replace = [
                '%name%' => $full_name,
                '%address%' => $address,
                '%customer_id%' => $customer_id,
                '%billing_number%' => $billing->billing_number ?? 'Nomor tidak tersedia',
                '%paket-name%' => $paket_name,
                '%pakets%' => $paket_name,  // Ini akan digantikan dengan rincian paket
                '%account-bank%' => $accountBank,
                '%day%' => $day,
                '%periode%' => $periode,  // Menampilkan nama periode
            ];

            // Ambil rincian paket
            $pakets = Billing::with('billing_periode')  // Eager load relasi billing_periode
                ->select('paket_name', 'billing_periode_id', 'paket_price')
                ->where('customer_name', $customer_name)
                ->whereIn('status', ['BL', 'PL'])
                ->get();

            // Format rincian paket
            $paketsText = '';
            $totalAmount = 0;
            foreach ($pakets as $paket) {
                // Ambil nama periode berdasarkan relasi billing_periode
                $billingPeriodeName = $paket->billing_periode->name ?? 'Tidak ada periode';  // Mengambil periode dari relasi
                $paketsText .= sprintf(
                    "- Paket: %s\n- Periode: %s\n- Harga: Rp. %s\n",
                    $paket->paket_name,
                    $billingPeriodeName,  // Menampilkan nama periode yang diambil dari relasi billing_periode
                    number_format($paket->paket_price, 2)
                );
                $totalAmount += $paket->paket_price;
            }

            // Update replace dengan rincian paket dan total tagihan
            $replace['%pakets%'] = $paketsText;
            $replace['%total-bill%'] = 'Rp.' . number_format($totalAmount, 2);

            // Penanganan berdasarkan aksi
            switch ($action) {
                case 'warning_bill':
                    $message = $this->getDefaultMessage('past_due_warning');
                    $replace['%count-bill%'] = $billing ? $billing->total_bills : 0;
                    break;

                case 'deadline_bill':
                    $message = $this->getDefaultMessage('deadline_bill');
                    $replace['%count-bill%'] = $billing ? $billing->total_bills : 0;
                    break;

                case 'reminder_bill':
                    $message = $this->getDefaultMessage('reminder_bill');
                    $replace['%count-bill%'] = $billing ? $billing->total_bills : 0;
                    break;

                default:
                    Log::error("Invalid action provided: $action");
                    return;
            }

            // Kirim pesan jika ada
            if ($message) {
                try {
                    $message = str_replace(array_keys($replace), $replace, $message);
                    dispatch(new SendWhatsappSingleJob($customer_contact, $gateway, $message))->onQueue('default');
                } catch (RequestException $e) {
                    Log::error("Failed to send WhatsApp message: {$e->getMessage()}");
                    return;
                }
            }
        } else {
            Log::error("Gateway or contact missing. Cannot send message.");
            return;
        }
    }

    //================================Manual send by admin=======================================
    public function broadcast_message($message) {}

    public function test_gateway($receive_number, $sender_number)
    {
        $gateway = $this->getGateway();

        // Cari instance WhatsappNumber berdasarkan nomor
        $whatsapp_number = WhatsappNumber::where('number', $sender_number)->first();

        if (!$whatsapp_number) {
            Log::error("Whatsapp Number not found for number: {$sender_number}");
            return redirect()->back()->with('error', 'Whatsapp number not found.');
        }

        //Log::info("Whatsapp Number Found: " . json_encode($whatsapp_number));

        // Cari instance WhatsappGateway berdasarkan whatsapp_number_id
        //$gateway = WhatsappGateway::where('whatsapp_number_id', $whatsapp_number->id)->first();

        if (!$gateway) {
            Log::error("Gateway not found for whatsapp_number_id: {$whatsapp_number->id}");
            return redirect()->back()->with('error', 'Gateway not found.');
        }

        //Log::info("Gateway Info: " . json_encode($gateway));

        $message = 'Congrulation!! Test message from Customer Management WA Gateway';

        if ($receive_number) {
            try {
                // Dispatch job dengan instance WhatsappGateway
                dispatch(new SendWhatsappSingleJob($receive_number, $gateway, $message))->onQueue('default');
            } catch (RequestException $e) {
                return redirect()->back()->with('error', 'Receive Number Required.');
            }
        }
    }
}
