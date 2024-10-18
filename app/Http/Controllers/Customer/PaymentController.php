<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Models\Billing\Billing;
use App\Http\Controllers\Controller;
use App\Models\Billing\BillingPeriode;
use App\Models\Websystem;

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
        // $orderId = Order::where('billing_id', $billing->id)->value('id');
        // return dd($orderId);
        // $order = '';
        //if ($orderId == NULL) {
        //    $orderStatus = 'NOTFOUND';
        // } else {
        //     $orderStatus = 'FOUND';
        //      $order = Order::where('id', $orderId)->first();
        //   }
        //$order = Order::where('id', $orderId)->first();
        // return view('customers.pages.payments.show', compact('billing', 'orderStatus', 'order'));
        $websystem = Websystem::where('slug', 'websystem')->first();
        // $paymentGateway = Websystem::where('slug', 'websystem')->value('payment_gateway');
        return view('customers.pages.payments.show', compact('billing', 'websystem'));
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
