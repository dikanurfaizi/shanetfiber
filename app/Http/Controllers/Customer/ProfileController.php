<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Billing\Billing;
use App\Models\Order;
use App\Models\Websystem;
use Jenssegers\Agent\Agent;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customerId = (auth()->guard('customer')->user()->id);
        // $bills = Billing::where('customer_id', $customerId)->get();
        // $orders = Order::where('customer_id', $customerId)->where('payment_status', 1)->orWhere('payment_status', 3)->orWhere('payment_status', 3)->get();'!='
        $billings = Billing::where('customer_id', $customerId)->where('status', 'BL')->get();
        $customer = Customer::where('id', $customerId)->first();
        // return dd($customer);
        $websystem = Websystem::where('slug', 'websystem')->first();
        $agent = new Agent();
        $result = $agent->isMobile();
        if ($result) {
            return view('mobile.customers.pages.dashboard', compact('customer', 'billings', 'websystem'));
        } else {
            return view('customers.pages.dashboard', compact('customer', 'billings', 'websystem'));
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
