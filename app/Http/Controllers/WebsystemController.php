<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Websystem;
use Illuminate\Http\Request;

class WebsystemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function general()
    {
        $websystem = Websystem::where('slug', 'websystem')->first();
        return view('websystems.general', compact('websystem'));
    }
    public function update_general(Request $request, WebSystem $webSystem)
    {
        // dd($webSystem, $request);

        if ($request->tax_button == 'true') {
            $statusTax = true;
            $this->validate(
                $request,
                [
                    'tax_rate' => 'required|numeric|min:1|max:20',
                    'postal_code' => 'numeric|digits:5',
                    'phone' => 'digits_between:8,15',
                    'email' => 'email:rfc,dns'
                ],
                [
                    'tax_rate.required' => 'Tax rate required.',
                    //  'postal_code.digits'
                ]
            );
        } else {
            $statusTax = false;
        }
        $webSystem->update([
            'title' => $request->title,
            'enable_tax' => $statusTax,
            'tax_rate' => $request->tax_rate,
            'email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'phone' => $request->phone,
        ]);
        return redirect()->back()
            ->with('success', 'Updated successfully');
    }
    /**
     * Configuration customer.
     */
    public function customer()
    {
        $websystem = Websystem::where('slug', 'websystem')->first();
        return view('websystems.customer', compact('websystem'));
    }

    public function update_customer(Request $request, WebSystem $webSystem)
    {
        // return dd($webSystem->slug);


        $this->validate(
            $request,
            [
                'customer_code' => 'required',
                'long_customer_code' => 'required|numeric|min:3|max:25',
            ],
            [
                'customer_code.required' => 'Customer code required.',
                'long_customer_code.required' => 'Long customer code required.'
            ]
        );

        $webSystem->update([
            'customer_code' => $request->customer_code,
            'long_customer_code' => $request->long_customer_code,

        ]);

        $longCode = '%0' . $webSystem->long_customer_code . 'd';

        $customers = Customer::all();
        foreach ($customers as $customer) {
            $customerId = $webSystem->customer_code . sprintf('%02d', $customer->mikrotik_id) . '-' . sprintf($longCode, $customer->id);
            $customer->update([
                'customer_id' => $customerId
            ]);
        }
        return redirect()->back()
            ->with('success', 'Updated successfully');
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
    public function show(WebSystem $webSystem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WebSystem $webSystem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_paymentgateway(Request $request, WebSystem $webSystem)
    {
        if ($webSystem->payment_gateway == 'disable') {
            $status = 'enable';
            $messages = 'Payment gateway enable successfully.';
        } else {
            $status = 'disable';
            $messages = 'Payment gateway disable successfully.';
        }
        $webSystem->update([
            'payment_gateway' => $status,
        ]);
        return redirect()->back()
            ->with('success', $messages);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WebSystem $webSystem)
    {
        //
    }
}
