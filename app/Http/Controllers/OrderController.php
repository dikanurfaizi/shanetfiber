<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Helpers\LogHelper;
use App\Http\Controllers\PaymentGateway\TripayController;
use App\Models\Billings\Billing;
use App\Models\Customers\Customer;
use App\Models\Midtran;
use App\Models\Billings\Order;
use App\Models\PaymentGateway;
use App\Models\Websystem;
use App\Services\Midtrans\CreateSnapTokenService;
use App\Services\Whatsapp\GowaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private GowaService $gowaService;

    public function __construct(GowaService $gowaService)
    {
        $this->middleware('customerauth');
        $this->gowaService = $gowaService;
    }

    public function index()
    {

        $websystem = $this->getWebsystem();
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
        $websystem = $this->getWebsystem();
        if ($websystem->payment_gateway == 'enable') {
            $order = Order::create([
                'customer_id' => $billing->customer_id,
                'billing_id' => $billing->id,
                'number' => rand(10000000, 99999999) . str_pad($billing->customer_id . $billing->id, 5, STR_PAD_LEFT),
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
        // return dd($billing);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
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
        $billing = $order->biling;

        // Refresh order untuk memastikan relasi benar
        $order = Order::where([
            ['customer_id', $billing->customer_id],
            ['billing_id', $billing->id],
            ['id', $order->id],
        ])->first();

        $viewData = $this->getViewData($order);

        // Tentukan tampilan berdasarkan status pembayaran
        if (in_array($billing->status, ['BL', 'PL']) && $order->payment_status == 1) {
            return view('customers.pages.orders.tripays.show', $viewData);
        }

        if ($billing->status == 'LS' && $order->payment_status == 2) {
            return view('customers.pages.payments.tripays.show', $viewData);
        }

        // Jika belum termasuk kondisi di atas, tampilkan halaman dengan channels
        $tripay = new TripayController;
        $viewData['channels'] = $tripay->getPaymentChannels();

        return view('customers.pages.payments.tripays.show', $viewData);
    }

    public function checkStatus(Order $order)
    {
        // Ambil status pembayaran yang terbaru dari database
        $order = Order::find($order->id);

        Log::info('Order Payment Status Updated', [
            'order_id' => $order->id,
            'status' => $order->payment_status,
        ]);

        $viewData = $this->getViewData($order);

        // Kalau getViewData() mungkin return redirect, perlu dicek dulu
        if ($viewData instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json([
                'redirect' => $viewData->getTargetUrl(),
            ]);
        }

        // Pisahkan hasil render untuk payment-status dan payment-section
        return response()->json([
            'html' => view('customers.components.payment-status', ['order' => $order])->render(),  // hanya status yang ditampilkan di halaman utama
            'modal' => view('customers.components.order-status-modal', [
                'order' => $order,
                'billing' => $viewData['billing'],
                'websystem' => $viewData['websystem'],
                'gateway' => $viewData['paymentGateway'],
            ])->render(),
            'footer' => view('customers.components.payment-footer', ['order' => $order])->render(),
            'payment_status' => $order->payment_status,
        ]);
    }

    private function setView(Order $order)
    {
        $shouldShow = ! in_array($order->payment_method, ['SHOPEEPAY', 'DANA', 'QRIS', 'QRISC', 'QRIS2']);
        $isOVO = $order->payment_method === 'OVO';
        $contact = $order->billing->customer->contact ?? null;

        if ($isOVO && ! is_null($contact)) {
            $contact = preg_replace('/^628/', '08', $contact);
        }

        $vaNumber = $isOVO && ! is_null($contact) ? $contact : $order->va_number;

        return (object) [
            'shouldShow' => $shouldShow,
            'vaNumber' => $vaNumber,
        ];
    }

    private function getWebsystem()
    {
        return Websystem::firstWhere('slug', 'websystem');
    }

    private function getPaymentGateway()
    {
        return PaymentGateway::firstWhere('disabled', 'false');
    }

    private function getViewData(Order $order)
    {
        $websystem = $this->getWebsystem();
        if ($websystem->payment_gateway !== 'enable') {
            return redirect()->route('customer.dashboard')->with('error', 'You cannot use the payment gateway.');
        }

        $paymentGateway = $this->getPaymentGateway();
        if (! $paymentGateway) {
            return redirect()->route('customer.dashboard')->with('error', 'There is no active payment gateway.');
        }

        $setView = $this->setView($order);
        $expiredAt = $order->expired_time * 1000;
        $billing = $order->billing;

        return compact('billing', 'websystem', 'paymentGateway', 'order', 'expiredAt') + [
            'shouldShow' => $setView->shouldShow,
            'vaNumber' => $setView->vaNumber,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        return dd($order);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }

    public function tripayShow(Order $order)
    {
        $viewData = $this->getViewData($order);

        return view('customers.pages.orders.tripays.show', $viewData);
    }

    public function tripayCreate(Request $request, Billing $billing)
    {
        $traceId = (string) Str::uuid();
        if (! $billing) {
            LogHelper::logError(
                null,
                ['billing_id' => $order->billing_id ?? null],
                500,
                '[Tripay] Billing not found',
                $traceId ?? null
            );

            return redirect()->back()->with('error', 'Billing not found.');
        }

        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $customer = Customer::find($billing->customer_id);

        // Generate order number terlebih dahulu
        $orderNumber = rand(10000000, 99999999) . str_pad($billing->customer_id . $billing->id, 5, STR_PAD_LEFT);

        // Buat objek sementara order (belum simpan ke DB)
        $tempOrder = [
            'customer_id' => $billing->customer_id,
            'billing_id' => $billing->id,
            'number' => $orderNumber,
            'package' => $billing->billingSubscription->customerSubscription->package->name,
            'total_price' => (int) $billing->total_amount,
            'payment_status' => 1,
            'payment_method' => $request->input('payment_method'),
            'payment_gateway' => $request->input('payment_channel'),
            'icon_url' => $request->input('icon_url'),
            'created_at' => now(),
        ];

        LogHelper::logInfo(null, [
            'order_number' => $orderNumber,
            'customer_id' => $billing->customer_id,
            'billing_id' => $billing->id,
            'total_amount' => (int) $billing->total_amount,
            'payment_method' => $request->input('payment_method'),
            'payment_channel' => $request->input('payment_channel'),
        ], 200, '[Tripay] Create Transaction Request', $traceId);

        $tripay = new TripayController;
        $signature = $tripay->getSignature($tempOrder);
        $tempOrder['signature'] = $signature;

        LogHelper::logInfo(null, [
            'name' => optional($customer)->name,
            'email' => $customer->getEmailForPayment(),
            'contact' => optional($customer)->contac,
        ], 200, '[Tripay] Start to Request Transaction', $traceId);

        // Kirim request ke Tripay
        $reqTransaction = $tripay->requestTransaction(
            $tempOrder,
            optional($customer)->name,
            $customer->getEmailForPayment(),
            optional($customer)->contact
        );

        // Jika gagal
        if (! is_array($reqTransaction)) {
            $message = $reqTransaction['original']['message']
                ?? $reqTransaction['message']
                ?? 'Tripay transaction response is not valid.';

            LogHelper::logError(
                null,
                [
                    'customer_id' => $customer?->id,
                    'customer_name' => optional($customer)->name,
                    'customer_contact' => optional($customer)->contact,
                    'billing_id' => $billing->id,
                    'payment_method' => $request->input('payment_method'),
                    'payment_channel' => $request->input('payment_channel'),
                    'email' => optional($customer)->email ?? optional($customer->user)->email,
                    'raw_response' => json_encode($reqTransaction),
                    'error' => $message,
                ],
                500,
                'Gagal membuat transaksi Tripay.',
                $traceId
            );

            return redirect()->back()->with('error', "Failed to create transaction. Because: {$message}");
        }

        // Kalau berhasil, simpan order ke DB
        $order = Order::create([
            'customer_id' => $billing->customer_id,
            'billing_id' => $billing->id,
            'number' => $orderNumber,
            'package' => $billing->billingSubscription->customerSubscription->package->name,
            'total_price' => $billing->total_amount,
            'payment_status' => 1,
            'payment_method' => $request->input('payment_method'),
            'payment_gateway' => $request->input('payment_channel'),
            'icon_url' => $request->input('icon_url'),
            'signature' => $signature,
            'reference' => $reqTransaction['reference'],
            'payment_selection_type' => $reqTransaction['payment_selection_type'],
            'va_number' => $reqTransaction['pay_code'],
            'expired_time' => $reqTransaction['expired_time'],
            'checkout_url' => $reqTransaction['checkout_url'],
            'payment_name' => $reqTransaction['payment_name'],
            'total_fee' => $reqTransaction['total_fee'],
            'quantity' => $reqTransaction['order_items'][0]['quantity'] ?? 1,
            'total_amount' => ($reqTransaction['total_fee'] ?? 0) + $billing->total_amount,
            'qr_string' => $reqTransaction['qr_string'] ?? null,
            'qr_url' => $reqTransaction['qr_url'] ?? null,
            'merchant_ref' => $reqTransaction['merchant_ref'] ?? null,
            'payment_type' => $request->input('payment_type'),
        ]);

        $order->fresh();

        event(new OrderCreated($customer, $billing, $order, $traceId));

        return redirect()->route('customer.pages.order.tripays.show', $order->id)
            ->with('success', 'Order created successfully.');
    }

    public function paymentInstruction(Order $order)
    {
        try {
            $tripay = new TripayController;

            return $tripay->getInstruction($order);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancelPayment(Order $order)
    {
        $order->update([
            'payment_status' => '4',
        ]);

        return redirect()->route('customer.payment.index')->with('success', 'Successfully cancel payment');
    }
}
