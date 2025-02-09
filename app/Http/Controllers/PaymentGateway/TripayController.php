<?php

namespace App\Http\Controllers\PaymentGateway;

use App\Models\Tripay;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class TripayController extends Controller
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
    public function show(Tripay $tripay)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tripay $tripay)
    {
        return view('payment_gateways.tripays.edit', compact('tripay'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tripay $tripay)
    {
        Log::info("Request nya: {$request}");
        if ($request->disabled) {
            // $disabled = 'true';
            $request->request->add(['disabled' => 'true']);
            $request->request->add(['tripays_is_production' => 'false']);
        } else {
            // $disabled = 'false';
            $request->request->add(['disabled' => 'false']);
            if ($request->tripays_is_production == NULL) {
                $request->request->add(['tripays_is_production' => 'false']);
                // $tripays_is_production = 'false';
                $this->validate(
                    $request,
                    [
                        'tripays_merchant_id' => 'required',
                        'tripays_sandbox_api_key' => 'required',
                        'tripays_sandbox_private_key' => 'required',
                    ],
                    [
                        'tripays_merchant_id.required' => 'Merchant id required if enable Tripays payment gateway.',
                        'tripays_sandbox_api_key.required' => 'Sandbox client key required to try payment gateway.',
                        'tripays_sandbox_private_key.required' => 'Sandbox server key required to try payment gateway.'

                    ]
                );
            } else {

                //$tripays_is_production = 'true';
                $request->request->add(['tripays_is_production' => 'true']);
                $this->validate(
                    $request,
                    [
                        'tripays_merchant_id' => 'required',
                        'tripays_api_key' => 'required',
                        'tripays_private_key' => 'required',
                    ],
                    [
                        'tripays_merchant_id.required' => 'Merchant id required if enable Tripays payment gateway.',
                        'tripays_api_key.required' => 'Production client key required for production mode.',
                        'tripays_private_key.required' => 'Production server key required for production mode.'

                    ]
                );
            }
        }

        $input = $request->all();
        $tripay->update($input);
        return redirect()->back()->with('success', 'Configuration tripays successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tripay $tripay)
    {
        //
    }
}
