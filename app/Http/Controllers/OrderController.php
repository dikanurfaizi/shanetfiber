<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Midtran;
use App\Models\Websystem;
use App\Models\Billing\Billing;
use App\Models\Customer;
use App\Models\PaymentGateway;
use App\Models\Receipt;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Pppoe\SecretResource;
use App\Http\Controllers\PaymentGateway\TripayController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Helpers\LogHelper;
use App\Services\Midtrans\CreateSnapTokenService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\IpStaticService;
use App\Jobs\AddPaymentAfterDueDateJob;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Jobs\AddPaymentIpStaticAfterDueDateJob;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private SecretService $secretService;
    private IpStaticService $ipStaticService;
    public function __construct(SecretService $secretService, IpStaticService $ipStaticService)
    {
        $this->middleware('customerauth');
        $this->secretService = $secretService;
        $this->ipStaticService = $ipStaticService;
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
            ['id', $order->id]
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
        $tripay = new TripayController();
        $viewData['channels'] = $tripay->getPaymentChannels();

        return view('customers.pages.payments.tripays.show', $viewData);
    }

    public function checkStatus(Order $order)
    {
        // Ambil status pembayaran yang terbaru dari database
        $order = Order::find($order->id);

        Log::info('Order Payment Status Updated', [
            'order_id'   => $order->id,
            'status' => $order->payment_status
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
                'order'     => $order,
                'billing'   => $viewData['billing'],
                'websystem' => $viewData['websystem'],
                'gateway'   => $viewData['paymentGateway'],
            ])->render(),
            'footer' => view('customers.components.payment-footer', ['order' => $order])->render(),
            'payment_status' => $order->payment_status,
        ]);
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

    public function processPaymentUnpaid($order, $billing)
    {
        $customer = Customer::find($order->customer_id);

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

    public function processPaymentSuccess($order, $billing, $payment_time)
    {
        $customer = $order->customer ?? Customer::find($order->customer_id);
        $user = auth()->user() ?? $customer->user;
        $now = $payment_time ?? Carbon::now();

        // Customer data
        $customerData = [
            'paket_id' => $customer->paket_id,
            'secret_id' => $customer->secret_id,
            'username' => $customer->username,
            'activation_date' => Carbon::parse($customer->activation_date)->format('d'),
            'mikrotik_id' => $customer->mikrotik_id,
            'internet_service' => $customer->internet_service,
            'ip_static' => $customer->ip_static,
            'simpleque_id' => $customer->simpleque_id,
        ];

        // Get related models
        $periodeName = $billing->billing_periode->name;
        $nextMonth = Carbon::parse($periodeName)->addMonth()->format('m_Y');
        $customerProfile = $customer->paket->profile;
        $router = Mikrotik::where('id', $customerData['mikrotik_id'])->first();
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customerData['mikrotik_id'])->first();

        // Handle auto isolir comment
        if ($router && $autoIsolir && $autoIsolir->disabled == 'false' && $router->disabled == 'false') {
            $comment_payment = $autoIsolir->activation_date == 'false'
                ? $autoIsolir->comment_payment
                : $this->generateCommentPayment($autoIsolir, $customerData['activation_date'], $nextMonth);

            $this->processMikrotikPayment($customerData, $customerProfile, $router, $autoIsolir, $comment_payment);
        }

        // Handle customer billing options and discounts
        [$discount, $priceAfterDiscount, $member] = $this->handleCustomerBilling($customer, $billing);

        // Create receipt
        $receiptNumber = $this->generateReceiptNumber();

        $receiptData = [
            'slug' => $billing->slug,
            'periode' => $periodeName,
            'number' => $receiptNumber,
            'billing_number' => $billing->billing_number,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_address' => $customer->address,
            'paket_name' => $order->package ?? $customer->paket->name,
            'paket_price' => $customer->paket->price,
            'discount' => $discount,
            'price_after_discount' => $priceAfterDiscount,
            'member_type' => $member,
            'created_name' => $customer->name,
            'teller_name' => optional($user)->name ?? 'Unknown',
            'payment_time' => $now,
            'payment_methode' => $order->payment_method,
            'bank_name' => $order->payment_name,
            'account_name' => $order->payment_name,
            'account_number' => $order->va_number,
            'deposit_bank_name' => $order->payment_method === 'transfer' ? $order->payment_name : null,
            'deposit_account_name' => $order->payment_method === 'transfer' ? $order->payment_name : null,
            'deposit_account_number' => $order->payment_method === 'transfer' ? $order->va_number : null,
            'reference_id' => $order->number,
            'tax' => $billing->tax,
        ];

        $receipt = Receipt::create($receiptData);

        // Update billing and order
        $billing->update([
            'payment_time' => $now,
            'teller_name' => optional($user)->name ?? 'Unknown',
            'status' => 'LS',
            'receipt_id' => $receipt->id,
        ]);

        if ($order) {
            $order->update(['payment_time' => $now]);
        }

        return $receipt;
    }

    // Helper methods
    private function generateCommentPayment($autoIsolir, $activationDate, $nextMonth)
    {
        $totalDaysInMonth = Carbon::now()->addMonth()->daysInMonth;
        $adjustedDate = min($activationDate, $totalDaysInMonth);
        return $autoIsolir->comment_unpayment . '_' . $adjustedDate . '_' . $nextMonth;
    }

    private function processMikrotikPayment($customerData, $customerProfile, $router, $autoIsolir, $comment_payment)
    {
        try {
            if ($customerData['internet_service'] == 'ppp') {
                $userSecretId = $this->secretService->getSecretById($router, $customerData['secret_id']);

                if ($userSecretId == 'error') {
                    throw new \Exception($customerData['secret_id'] . '! Cannot find user "' . $customerData['username'] . '" on server ' . $router->name . '.');
                }

                $secretProfiles = SecretResource::collection($userSecretId)->value('profile');
                $job = ($secretProfiles == $customerProfile)
                    ? new AddPaymentBeforeDueDateJob($router, $comment_payment, $customerData['secret_id'])
                    : new AddPaymentAfterDueDateJob($router, $customerProfile, $comment_payment, $customerData['secret_id'], $customerData['username']);

                dispatch($job)->onQueue('default');
            } elseif ($customerData['internet_service'] == 'ip_static') {
                $comment = $customerData['username'] . '_' . $comment_payment;
                $addressListCustomer = $this->ipStaticService->getAddressListByIpAddress($router, $customerData['ip_static']);

                if (isset($addressListCustomer[0]['list']) && $addressListCustomer[0]['list'] != $autoIsolir->address_list_isolir) {
                    dispatch(new AddPaymentBeforeDueDateJob($router, $customerData['simpleque_id'], $comment_payment))->onQueue('default');
                } else {
                    dispatch(new AddPaymentIpStaticAfterDueDateJob(
                        $router,
                        $customerData['ip_static'],
                        $customerData['simpleque_id'],
                        $autoIsolir->address_list_isolir,
                        $comment_payment,
                        $comment
                    ))->onQueue('default');
                }
            }
        } catch (\Exception $e) {
            Log::error('Mikrotik payment processing failed: ' . $e->getMessage());
        }
    }

    private function handleCustomerBilling($customer, $billing)
    {
        $discount = 0;
        $member = 'Non Member / Tidak Ada';
        $priceAfterDiscount = $customer->paket->price;

        if ($customer->billing_option == 1) {
            // Case 1: Hitung discount tapi tidak update data customer
            $discount = optional($customer->member)->discount ?? 0;
            $priceAfterDiscount = $customer->paket->price - $discount;
            $member = optional($customer->member)->type;
        } elseif ($customer->billing_option == 2 && $billing->billing_periode->name === $customer->billing_period) {
            // Case 2: Hitung discount DAN update data customer
            $discount = optional($customer->member)->discount ?? 0;
            $priceAfterDiscount = $customer->paket->price - $discount;
            $member = optional($customer->member)->type;

            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id' => null,
                'discount' => '0',
            ]);
        } else {
            // Case 3: Update data customer tanpa menghitung discount
            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id' => null,
                'discount' => '0',
            ]);
        }

        return [$discount, $priceAfterDiscount, $member];
    }

    private function generateReceiptNumber()
    {
        $lastReceipt = Receipt::latest('created_at')->value('number');
        $number = $lastReceipt ? (int) explode('-', $lastReceipt)[1] + 1 : 1;

        return sprintf("KWT-%05d-%s-%s", $number, Carbon::now()->format('F'), Carbon::now()->format('Y'));
    }

    public function processUpdatePaymentStatus($order, $status)
    {
        $order->update([
            'payment_status' => $status
        ]);
    }

    private function setView(Order $order)
    {
        $shouldShow = !in_array($order->payment_method, ['SHOPEEPAY', 'DANA', 'QRIS', 'QRISC', 'QRIS2']);
        $isOVO = $order->payment_method === 'OVO';
        $contact = $order->billing->customer->contact ?? null;

        if ($isOVO && !is_null($contact)) {
            $contact = preg_replace('/^628/', '08', $contact);
        }

        $vaNumber = $isOVO && !is_null($contact) ? $contact : $order->va_number;

        return (object)[
            'shouldShow' => $shouldShow,
            'vaNumber'   => $vaNumber,
        ];
    }

    private function getWebsystem()
    {
        return Websystem::firstWhere('slug', 'websystem');;
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
        if (!$paymentGateway) {
            return redirect()->route('customer.dashboard')->with('error', 'There is no active payment gateway.');
        }

        $setView = $this->setView($order);
        $expiredAt = $order->expired_time * 1000;
        $billing = $order->billing;

        return compact('billing', 'websystem', 'paymentGateway', 'order', 'expiredAt') + [
            'shouldShow' => $setView->shouldShow,
            'vaNumber'   => $setView->vaNumber,
        ];
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
        $viewData = $this->getViewData($order);
        return view('customers.pages.orders.tripays.show', $viewData);
    }

    public function tripayCreate(Request $request, Billing $billing)
    {
        if (!$billing) {
            return redirect()->back()->with('error', 'Billing not found.');
        }

        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $customer = Customer::find($billing->customer_id);

        // Generate order number terlebih dahulu
        $orderNumber = rand(10000000, 99999999) . str_pad($billing->customer_id . $billing->id, 5, STR_PAD_LEFT);

        // Buat objek sementara order (belum simpan ke DB)
        $tempOrder = new Order([
            'customer_id'     => $billing->customer_id,
            'billing_id'      => $billing->id,
            'number'          => $orderNumber,
            'package'         => $billing->paket_name,
            'total_price'     => $billing->paket_price + $billing->tax,
            'payment_status'  => 1,
            'payment_method'  => $request->input('payment_method'),
            'payment_gateway' => $request->input('payment_channel'),
            'icon_url'        => $request->input('icon_url'),
        ]);

        $tripay = new TripayController();
        $signature = $tripay->getSignature($tempOrder);
        $tempOrder->signature = $signature;

        // Kirim request ke Tripay
        $reqTransaction = $tripay->requestTransaction(
            $tempOrder,
            optional($customer)->name,
            optional($customer)->email,
            optional($customer)->contact
        );

        // dd($reqTransaction);

        // Jika gagal
        if (!is_array($reqTransaction)) {
            LogHelper::logError(
                null,
                [
                    'customer_id' => $customer?->id,
                    'billing_id'  => $billing->id,
                    'payment_method' => $request->input('payment_method'),
                    'payment_channel' => $request->input('payment_channel'),
                    'raw_response' => json_encode($reqTransaction),
                    'error' => 'Tripay transaction response is not valid.'
                ],
                500,
                "Gagal membuat transaksi Tripay."
            );

            return redirect()->back()->with('error', 'Gagal membuat transaksi.');
        }


        // Kalau berhasil, simpan order ke DB
        $order = Order::create([
            'customer_id'     => $billing->customer_id,
            'billing_id'      => $billing->id,
            'number'          => $orderNumber,
            'package'         => $billing->paket_name,
            'total_price'     => $billing->paket_price + $billing->tax,
            'payment_status'  => 1,
            'payment_method'  => $request->input('payment_method'),
            'payment_gateway' => $request->input('payment_channel'),
            'icon_url'        => $request->input('icon_url'),
            'signature'       => $signature,
            'reference'       => $reqTransaction['reference'],
            'payment_selection_type' => $reqTransaction['payment_selection_type'],
            'va_number'       => $reqTransaction['pay_code'],
            'expired_time'    => $reqTransaction['expired_time'],
            'checkout_url'    => $reqTransaction['checkout_url'],
            'payment_name'    => $reqTransaction['payment_name'],
            'total_fee'       => $reqTransaction['total_fee'],
            'quantity'        => $reqTransaction['order_items'][0]['quantity'] ?? 1,
            'total_amount'    => ($reqTransaction['total_fee'] ?? 0) + $billing->paket_price + $billing->tax,
            'qr_string'       => $reqTransaction['qr_string'] ?? null,
            'qr_url'          => $reqTransaction['qr_url'] ?? null,
            'merchant_ref'    => $reqTransaction['merchant_ref'] ?? null
        ]);

        return redirect()->route('customer.pages.order.tripays.show', $order->id)
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

    public function cancelPayment(Order $order)
    {
        $order->update([
            'payment_status' => '4'
        ]);

        return redirect()->route('customer.payment.index')->with('success', 'Successfully cancel payment');
    }
}
