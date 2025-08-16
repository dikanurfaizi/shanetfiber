<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Models\Billing\Billing;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Services\Customers\PaymentService;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $traceId = (string) Str::uuid();
        $customerId = auth()->guard('customer')->user()->id;
        $state = request('status');
        $perPage = request('per_page', 10);

        $billings = $this->paymentService->getBillings($customerId, $state, $perPage, $traceId);
        $websystem = $this->paymentService->getWebsystem($traceId);

        return view('customers.pages.payments.index', compact('billings', 'websystem'))
            ->with('i', (request()->input('page', 1) - 1) * $perPage);
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
        $traceId = (string) Str::uuid();
        $result = $this->paymentService->getPaymentDetail($billing, $traceId);

        return view($result['view'], $result['data']);
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
