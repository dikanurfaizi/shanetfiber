<?php

namespace App\Http\Controllers\PaymentGateway;

use App\Models\Websystem;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:is-admin', ['only' => ['index', 'edit']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentGateways = PaymentGateway::paginate(10);
        $webSystem = Websystem::where('slug', 'websystem')->first();

        // Transform status for each gateway
        $paymentGateways->getCollection()->transform(function ($gateway) {
            $isDisabled = filter_var($gateway->disabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $gateway->status = $isDisabled == true ? 'Disabled' : 'Enabled';
            return $gateway;
        });

        return view('payment_gateways.index', [
            'paymentGateways' => $paymentGateways,
            'webSystem' => $webSystem,
            'i' => (request()->input('page', 1) - 1) * 10
        ]);
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
    public function show(PaymentGateway $paymentGateway)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentGateway $paymentGateway)
    {
        $slug = $paymentGateway->slug;
        if ($slug == 'midtrans') {
            return redirect()->route('paymentgateway.midtran.edit', $slug);
        } else if ($slug == 'tripays') {
            return redirect()->route('paymentgateway.tripay.edit', $slug);
        } else if ($slug == 'OY') {
            return dd('OY Access');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentGateway $paymentGateway)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentGateway $paymentGateway)
    {
        //
    }
}
