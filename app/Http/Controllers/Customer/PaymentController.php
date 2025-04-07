<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Models\Billing\Billing;
use App\Http\Controllers\Controller;
use App\Models\Billing\BillingPeriode;
use App\Models\Websystem;
use App\Models\PaymentGateway;
use App\Http\Controllers\PaymentGateway\TripayController;
use App\Models\Order;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customerId = (auth()->guard('customer')->user()->id);
        $websystem = Websystem::where('slug', 'websystem')->first();
        $state = request('status');
        $billings = Billing::where('customer_id', $customerId)
            ->when($state, function ($query) use ($state) {
                $query->where('status', $state);
            })
            // ->orderBy('year', 'DESC')
            // ->orderBy('month', 'DESC')
            ->orderBy(
                BillingPeriode::select('created_at')
                    ->whereColumn('billing_periodes.id', 'billings.billing_periode_id'),
                'DESC'
            )
            ->orderBy('customer_name', 'ASC')
            ->paginate(10);

        return view('customers.pages.payments.index', compact('billings', 'websystem'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
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
    public function show(Billing $billing)
    {
        $websystem = Websystem::where('slug', 'websystem')->first();
        if ($websystem->payment_gateway === 'enable') {
            $payment_gateways = PaymentGateway::where('disabled', 'false')->get();

            foreach ($payment_gateways as $gateway) {
                if ($gateway->slug === 'midtrans') {
                    // Action untuk midtrans
                    return view('customers.pages.payments.show', compact('billing', 'websystem'));
                } elseif ($gateway->slug === 'tripays') {
                    // Action untuk tripays
                    $tripay = new TripayController();
                    $channels = $tripay->getPaymentChannels();
                    $order = Order::where('orders.customer_id', $billing->customer_id)
                        ->where('orders.billing_id', $billing->id)
                        ->where('orders.payment_status', \App\Models\Order::STATUS_UNPAID) // atau langsung 1
                        ->first();

                    // dd($order);

                    return view('customers.pages.payments.tripays.show', compact('billing', 'websystem', 'gateway', 'channels', 'order'));
                } else {
                    // Action default jika ada yang lain
                    return view('errors.notfound');
                }
            }
        }
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
