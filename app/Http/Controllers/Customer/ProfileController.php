<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Billing\Billing;
use App\Models\Bank;
use App\Models\Websystem;
use Jenssegers\Agent\Agent;
use App\Helpers\LogHelper;

class ProfileController extends Controller
{
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
     * Display a listing of the resource.
     */
    public function index()
    {
        $customerId = (auth()->guard('customer')->user()->id);
        // $bills = Billing::where('customer_id', $customerId)->get();
        // $orders = Order::where('customer_id', $customerId)->where('payment_status', 1)->orWhere('payment_status', 3)->orWhere('payment_status', 3)->get();'!='
        $billings = Billing::where('customer_id', $customerId)->where('status', 'BL')->get();
        $last_payment = Billing::where('customer_id', $customerId)->where('status', 'LS')->orderBy('payment_time', 'DESC')->first();
        $customer = Customer::where('id', $customerId)->first();
        $contact_admin = $this->manipulateContactBillInfo($customer->user->contact, null);
        $banks = Bank::where('user_id', $customer->user_id)->where('disabled', 'false')->get();
        // dd($admin);
        // return dd($customer);
        $websystem = Websystem::where('slug', 'websystem')->first();
        $agent = new Agent();
        $result = $agent->isMobile();
        if ($result) {
            return view('mobile.customers.pages.dashboard', compact('customer', 'billings', 'websystem', 'last_payment', 'banks', 'contact_admin'));
        } else {
            return view('customers.pages.dashboard', compact('customer', 'billings', 'websystem', 'last_payment', 'banks', 'contact_admin'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $customerId = (auth()->guard('customer')->user()->id);
        $customer = Customer::where('id', $customerId)->first();
        // return dd($customer);
        return view('customers.pages.profile', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
