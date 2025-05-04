<?php

namespace App\Http\Controllers\PaymentGateway;

use App\Models\Midtran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MidtranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(Midtran $midtran)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Midtran $midtran)
    {
        return view('payment_gateways.midtrans.edit', compact('midtran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Midtran $midtran)
    {
        // dd($request);
        if ($request->disabled === "true") {
            // $disabled = 'true';
            $request->request->add(['disabled' => 'true']);
            $request->request->add(['midtrans_is_production' => 'false']);
        } else {
            //$disabled = 'false';
            $request->request->add(['disabled' => 'false']);
            // dd($request->midtrans_is_production);
            if ($request->midtrans_is_production === "false") {
                $request->request->add(['midtrans_is_production' => 'false']);
                // $midtrans_is_production = 'false';
                $this->validate(
                    $request,
                    [
                        'midtrans_merchant_id' => 'required',
                        'midtrans_sandbox_client_key' => 'required',
                        'midtrans_sandbox_server_key' => 'required',
                    ],
                    [
                        'midtrans_merchant_id.required' => 'Merchant id required if enable Midtrans payment gateway.',
                        'midtrans_sandbox_client_key.required' => 'Sandbox client key required to try payment gateway.',
                        'midtrans_sandbox_server_key.required' => 'Sandbox server key required to try payment gateway.'

                    ]
                );
            } else {

                //$midtrans_is_production = 'true';
                $request->request->add(['midtrans_is_production' => 'true']);
                $this->validate(
                    $request,
                    [
                        'midtrans_merchant_id' => 'required',
                        'midtrans_client_key' => 'required',
                        'midtrans_server_key' => 'required',
                    ],
                    [
                        'midtrans_merchant_id.required' => 'Merchant id required if enable Midtrans payment gateway.',
                        'midtrans_client_key.required' => 'Production client key required for production mode.',
                        'midtrans_server_key.required' => 'Production server key required for production mode.'

                    ]
                );
            }
        }

        $input = $request->all();
        $midtran->update($input);
        return redirect()->back()->with('success', 'Configuration midtrans successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Midtran $midtran)
    {
        //
    }
}
