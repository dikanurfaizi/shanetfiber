<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Midtran;
use App\Models\Websystem;
use Illuminate\Http\Request;
use App\Models\Billing\Billing;
use App\Http\Controllers\Controller;
use App\Services\Midtrans\CreateSnapTokenService;
use App\Models\PaymentGateway;
use App\Http\Controllers\PaymentGateway\TripayController;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('customerauth');
    }
    public function index()
    {

        $websystem = Websystem::where('slug', 'websystem')->first();
        if ($websystem->payment_gateway == 'enable') {
            $customerId = (auth()->guard('customer')->user()->id);
            // $orders = Order::where('customer_id', $customerId)->paginate(10);

            $state = request('status');
            $orders = Order::where('customer_id', $customerId)
                ->when($state, function ($query) use ($state) {
                    $query->where('payment_status', $state);
                })
                ->orderBy('created_at', 'ASC')
                ->paginate(10);


            return view('customers.pages.orders.index', compact('orders', 'websystem'))
                ->with('i', (request()->input('page', 1) - 1) * 10);
        } else {
            return redirect()->route('customer.dashboard')
                ->with('error', 'You canot use payment gateway');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Billing $billing)
    {
        // return dd($billing);
        $websystem = Websystem::where('slug', 'websystem')->first();
        if ($websystem->payment_gateway == 'enable') {
            $order = Order::create([
                'customer_id' => $billing->customer_id,
                'billing_id' => $billing->id,
                'number' => rand(10000000, 99999999) . str_pad($billing->customer_id . $billing->id,  5, STR_PAD_LEFT),
                'total_price' => $billing->paket_price + $billing->tax,
                'payment_status' => 1,
            ]);
            $getOrder = $order->fresh();

            return redirect()->route('customer.order.show', $getOrder->id)
                ->with('success', 'Order created successfully.');
        } else {
            return redirect()->route('customer.dashboard')
                ->with('error', 'You canot use payment gateway');
        }
        //return dd($billing);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $paymentGateway = Websystem::where('slug', 'websystem')->value('payment_gateway');
        if ($paymentGateway == 'enable') {
            // $clientKey = Midtran::where('id', 1)->value('midtrans_client_key');
            $midtrans = Midtran::where('slug', 'midtrans')->first();
            //return dd($midtrans->midtrans_is_production);
            if ($midtrans->midtrans_is_production == 'false') {
                $clientKey = $midtrans->midtrans_sandbox_client_key;
                $midtransJs = 'https://app.sandbox.midtrans.com/snap/snap.js';
            } else {
                $clientKey = $midtrans->midtrans_client_key;
                $midtransJs = 'hhttps://app.midtrans.com/snap/snap.js';
            }
            $snapToken = $order->snap_token;
            if (is_null($snapToken)) {
                // Jika snap token masih NULL, buat token snap dan simpan ke database
                // return dd($order);
                $midtrans = new CreateSnapTokenService($order);
                $snapToken = $midtrans->getSnapToken();

                $order->snap_token = $snapToken;
                $order->save();
            }

            return view('customers.pages.orders.show', compact('order', 'snapToken', 'clientKey', 'midtransJs'));
        } else {
            return redirect()->route('customer.dashboard')
                ->with('error', 'You canot use payment gateway');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        return dd($order);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }

    public function tripayShow(Order $order)
    {
        if (Websystem::where('slug', 'websystem')->value('payment_gateway') !== 'enable') {
            return redirect()->route('customer.dashboard')->with('error', 'You cannot use the payment gateway.');
        }

        $customer = Customer::find($order->customer_id);

        $order->customer_name  = optional($customer)->name;
        $order->customer_email = optional($customer)->email;
        $order->customer_phone = optional($customer)->contact;

        $tripay = new TripayController();
        $reqTransaction = $tripay->requestTransaction($order);

        return view('customers.pages.orders.tripays.show', compact('reqTransaction', 'order'));
    }

    public function tripayCreate(Request $request, Billing $billing)
    {
        if (!$billing) {
            return redirect()->back()->with('error', 'Billing not found.');
        }

        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $websystem = Websystem::where('slug', 'websystem')->first();
        if ($websystem->payment_gateway !== 'enable') {
            return redirect()->route('customer.dashboard')->with('error', 'You cannot use the payment gateway.');
        }

        $order = Order::create([
            'customer_id'            => $billing->customer_id,
            'billing_id'             => $billing->id,
            'number'                 => rand(10000000, 99999999) . str_pad($billing->customer_id . $billing->id, 5, STR_PAD_LEFT),
            'package'                => $billing->paket_name,
            'total_price'            => $billing->paket_price + $billing->tax,
            'payment_status'         => 1,
            'payment_channel_code'   => $request->input('payment_method'),
            'payment_gateway'        => $request->input('payment_channel'),
            'icon_url'               => $request->input('icon_url'),
        ]);

        $tripay = new TripayController();
        $order->update([
            'signature' => $tripay->getSignature($order),
        ]);

        return redirect()->route('customer.pages.order.tripays.show', $order->fresh()->id)
            ->with('success', 'Order created successfully.');
    }
}
