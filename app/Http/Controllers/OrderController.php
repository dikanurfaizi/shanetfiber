<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Midtran;
use App\Models\Websystem;
use Illuminate\Http\Request;
use App\Models\Billing\Billing;
use App\Http\Controllers\Controller;
use App\Services\Midtrans\CreateSnapTokenService;
use App\Http\Controllers\PaymentGateway\TripayController;
use App\Models\Tripay;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentGateway;
use Illuminate\Support\Carbon;
use App\Models\Bank;
use App\Models\Receipt;

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

        if ($paymentGateway !== 'enable') {
            return redirect()->route('customer.dashboard')
                ->with('error', 'You cannot use payment gateway');
        }

        $midtrans = Midtran::where('slug', 'midtrans')->first();
        if ($midtrans && $midtrans->disable === 'true') {
            return $this->handleMidtransPayment($order, $midtrans);
        }

        return $this->handleTripayPayment($order);
    }

    private function handleMidtransPayment($order, $midtrans)
    {
        $clientKey = $midtrans->midtrans_is_production === 'false'
            ? $midtrans->midtrans_sandbox_client_key
            : $midtrans->midtrans_client_key;

        $midtransJs = $midtrans->midtrans_is_production === 'false'
            ? 'https://app.sandbox.midtrans.com/snap/snap.js'
            : 'https://app.midtrans.com/snap/snap.js';

        $snapToken = $order->snap_token ?? $this->generateSnapToken($order);

        return view('customers.pages.orders.show', compact('order', 'snapToken', 'clientKey', 'midtransJs'));
    }

    private function generateSnapToken($order)
    {
        $midtrans = new CreateSnapTokenService($order);
        $snapToken = $midtrans->getSnapToken();

        $order->update(['snap_token' => $snapToken]);
        return $snapToken;
    }

    private function handleTripayPayment($order)
    {
        $websystem = Websystem::where('slug', 'websystem')->first();
        $gateway = PaymentGateway::where('disabled', 'false')->first();
        $tripay = new TripayController();
        $billing = Billing::find($order->billing_id);
        $channels = $tripay->getPaymentChannels();

        $statusResponse = $tripay->getStatusTransaction($order);
        $statusArray = json_decode($statusResponse->getContent(), true);

        if (!$statusArray || !isset($statusArray['message'])) {
            Log::error('Invalid response from Tripay', ['response' => $statusArray]);
            return response()->json(['error' => 'Invalid response from Tripay'], 500);
        }

        $paymentStatus = $this->getPaymentStatus($statusArray['message']);

        // dd($paymentStatus);

        if (!is_null($paymentStatus) && $order->payment_status !== $paymentStatus) {
            $order->update(['payment_status' => $paymentStatus]);
            Log::info('Order Payment Status Updated', [
                'order_id' => $order->id,
                'new_status' => $paymentStatus
            ]);

            if ($paymentStatus === 2) {
                $this->processPaymentSuccess($order, $billing);
            } else {
                $this->processPaymentUnpaid($order, $billing);
            }
        }

        return $paymentStatus === 2
            ? redirect()->route('customer.payment.show', ['billing' => $order->billing_id])
            : view('customers.pages.payments.tripays.show', compact('billing', 'websystem', 'gateway', 'channels', 'order'));
    }

    private function getPaymentStatus($message)
    {
        $statusMapping = [
            'UNPAID'  => 1,
            'DIBAYAR' => 2,
            'EXPIRED' => 3,
            'FAILED'  => 4,
        ];

        foreach ($statusMapping as $keyword => $status) {
            if (stripos($message, $keyword) !== false) {
                return $status;
            }
        }

        return null;
    }

    private function processPaymentUnpaid($order, $billing)
    {
        $customer = Customer::find($order->customer_id);
        $discount = $customer->member->discount ?? 0;
        $price_after_discount = $customer->paket->price - $discount;
        $member = $customer->member?->type ?? 'Non Member / Tidak Ada';

        if ($customer->billing_option == 2 && $billing->billing_periode->name === $customer->billing_period) {
            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id' => null,
                'discount' => '0'
            ]);
        }

        $receiptNumber = $billing->receipt_id;
        Receipt::where('id', $receiptNumber)->delete();

        $billing->update([
            'payment_time' => NULL,
            'teller_name' => optional($customer->user)->name ?? 'Unknown',
            'status' => 'BL',
            'receipt_id' => NULL,
        ]);
    }

    private function processPaymentSuccess($order, $billing)
    {
        $customer = Customer::find($order->customer_id);
        $discount = $customer->member->discount ?? 0;
        $price_after_discount = $customer->paket->price - $discount;
        $member = $customer->member?->type ?? 'Non Member / Tidak Ada';

        if ($customer->billing_option == 2 && $billing->billing_periode->name === $customer->billing_period) {
            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id' => null,
                'discount' => '0'
            ]);
        }

        $receiptNumber = $this->generateReceiptNumber();
        $receipt = Receipt::create([
            'slug' => $billing->slug,
            'periode' => $billing->billing_periode->name,
            'number' => $receiptNumber,
            'billing_number' => $billing->billing_number,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_address' => $customer->address,
            'paket_name' => $order->package,
            'paket_price' => $customer->paket->price,
            'discount' => $discount,
            'price_after_discount' => $price_after_discount,
            'member_type' => $member,
            'created_name' => $customer->name,
            'teller_name' => optional($customer->user)->name ?? 'Unknown',
            'payment_time' => Carbon::now(),
            'payment_methode' => 'tripay payment',
            'bank_name' => $order->payment_name,
            'account_name' => $order->payment_name,
            'account_number' => $order->va_number,
            'deposit_bank_name' => null,
            'deposit_account_name' => null,
            'deposit_account_number' => null,
            'reference_id' => $order->number,
            'tax' => $billing->tax,
        ]);

        $billing->update([
            'payment_time' => Carbon::now(),
            'teller_name' => optional($customer->user)->name ?? 'Unknown',
            'status' => 'LS',
            'receipt_id' => $receipt->id,
        ]);
    }

    private function generateReceiptNumber()
    {
        $lastReceipt = Receipt::latest('created_at')->value('number');
        $number = $lastReceipt ? (int) explode('-', $lastReceipt)[1] + 1 : 1;

        return sprintf("KWT-%05d-%s-%s", $number, Carbon::now()->format('F'), Carbon::now()->format('Y'));
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

        // dd($order);

        return view('customers.pages.orders.tripays.show', compact('order'));
    }

    public function tripayCreate(Request $request, Billing $billing)
    {
        if (!$billing) {
            return redirect()->back()->with('error', 'Billing not found.');
        }

        $request->validate(['payment_method' => 'required|string']);

        $websystem = Websystem::where('slug', 'websystem')->first();
        if ($websystem->payment_gateway !== 'enable') {
            return redirect()->route('customer.dashboard')->with('error', 'You cannot use the payment gateway.');
        }

        $order = Order::create([
            'customer_id'     => $billing->customer_id,
            'billing_id'      => $billing->id,
            'number'          => rand(10000000, 99999999) . str_pad($billing->customer_id . $billing->id, 5, STR_PAD_LEFT),
            'package'         => $billing->paket_name,
            'total_price'     => $billing->paket_price + $billing->tax,
            'payment_status'  => 1,
            'payment_method'  => $request->input('payment_method'),
            'payment_gateway' => $request->input('payment_channel'),
            'icon_url'        => $request->input('icon_url'),
        ]);

        $tripay = new TripayController();
        $order->update(['signature' => $tripay->getSignature($order)]);

        $customer = Customer::find($order->customer_id);
        $reqTransaction = $tripay->requestTransaction(
            $order,
            optional($customer)->name,
            optional($customer)->email,
            optional($customer)->contact
        );

        if (!is_array($reqTransaction)) {
            throw new \Exception("Invalid transaction response");
        }

        $order->update([
            'reference'               => $reqTransaction['reference'],
            'payment_selection_type'  => $reqTransaction['payment_selection_type'],
            'va_number'               => $reqTransaction['pay_code'],
            'expired_time'            => $reqTransaction['expired_time'],
            'checkout_url'            => $reqTransaction['checkout_url'],
            'payment_name'            => $reqTransaction['payment_name'],
            'total_fee'               => $reqTransaction['total_fee'],
            'quantity'                => $reqTransaction['order_items'][0]['quantity'] ?? 1,
            'total_amount'            => $reqTransaction['total_fee'] + $order->total_price,
        ]);

        return redirect()->route('customer.pages.order.tripays.show', $order->fresh()->id)
            ->with('success', 'Order created successfully.');
    }

    public function paymentInstruction(Order $order)
    {
        try {
            $tripay = new TripayController();
            return $tripay->getInstruction($order);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
