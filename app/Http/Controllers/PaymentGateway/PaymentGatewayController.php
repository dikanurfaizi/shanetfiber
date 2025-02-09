<?php

namespace App\Http\Controllers\PaymentGateway;

use App\Models\Websystem;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        //$paymentGatewayStatus = Websystem::where('slug', 'websystem')->value('payment_gateway');
        $webSystem = Websystem::where('slug', 'websystem')->first();

        // Menampung status masing-masing payment gateway
        $gatewayStatuses = [];

        foreach ($paymentGateways as $gateway) {
            $tableName = $gateway->slug; // Slug merepresentasikan nama tabel

            // Cek apakah tabel ada di database sebelum query
            if (Schema::hasTable($tableName)) {
                // Ambil nilai `disabled` dari tabel terkait
                $status = DB::table($tableName)->value('disabled');

                // Konversi status ke "Enable" atau "Disable"
                $gatewayStatuses[$tableName] = $status ? 'Disabled' : 'Enabled';
            }
        }

        // Tambahkan hasil ke dalam webSystem response
        $paymentGateways->payment_gateway_statuses = $gatewayStatuses;

        return view('payment_gateways.index', compact('paymentGateways', 'webSystem'))
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
