<?php

namespace App\Http\Controllers\Midtran;

use App\Models\Order;
use App\Models\Midtran;
use App\Models\Websystem;
use Illuminate\Http\Request;
use App\Models\Billings\Billing;
use App\Http\Controllers\Controller;
use App\Services\Midtrans\CreateSnapTokenService;

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
                'total_price' => $billing->total_amount,
                'payment_status' => 1,
            ]);
            $getOrder = $order->fresh();

            return redirect()->route('customer.order.show', $getOrder->id)
                ->with('success', 'Order created successfully.');
        } else {
            return redirect()->route('customer.dashboard')
                ->with('error', 'You canot use payment gateway');
        }
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
}
