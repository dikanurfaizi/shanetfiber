<?php

namespace App\Http\Controllers;

use App\Models\Billing\BillingPeriode;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Thermal;
use Illuminate\Http\Request;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\Customer;
use App\Models\Websystem;
use Illuminate\Support\Facades\Log;

//use Barryvdh\DomPDF\PDF;

class ReceiptController extends Controller
{


    public function __construct()
    {
        $this->middleware('permission:billing-list|billing-create|billing-edit|billing-delete', ['only' => ['index', 'show', 'print', 'thermal']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $paginate = 25;
        $searchName = $request->search_name;
        $searchPeriode = $request->search_month . ' ' . $request->search_year;
        $searchAddress = $request->search_address;
        $paymentMethode = $request->payment_methode;
        $tellerName = $request->teller;

        $accountName = null;
        $bankName = null;
        $accountNumber = null;
        if ($request->account_bank) {
            $accountBank = explode('#', $request->account_bank);
            $accountName = $accountBank[0];
            $bankName = $accountBank[1];
            $accountNumber = $accountBank[2];
        }
        if (!$request->search_month && !$request->search_year && !$paymentMethode && !$searchAddress && !$request->account_bank) {
            $searchPeriode = BillingPeriode::latest('created_at')->value('name');
            //return dd($searchPeriode);
        }
        $invoices = Receipt::query()
            ->when($searchName, function ($query) use ($searchName) {
                $query->where('customer_name', 'like', "%{$searchName}%");
            })
            ->when($searchPeriode, function ($query) use ($searchPeriode) {
                $query->where('periode', 'like', "%{$searchPeriode}%");
            })
            ->when($searchAddress, function ($query) use ($searchAddress) {
                $query->where('customer_address', $searchAddress);
            })
            ->when($paymentMethode, function ($query) use ($paymentMethode) {
                $query->where('payment_methode', $paymentMethode);
            })
            ->when($accountName, function ($query) use ($accountName) {
                $query->where('account_name', $accountName);
            })
            ->when($bankName, function ($query) use ($bankName) {
                $query->where('bank_name', $bankName);
            })
            ->when($accountNumber, function ($query) use ($accountNumber) {
                $query->where('account_number', $accountNumber);
            })
            ->when($tellerName, function ($query) use ($tellerName) {
                $query->where('teller_name', $tellerName);
            })
            ->orderBy('created_at', 'ASC');
        $totalPrice = $invoices->sum('paket_price');
        $receipts = $invoices->paginate($paginate);

        return view('receipts.index', compact('receipts', 'totalPrice'))
            ->with('i', (request()->input('page', 1) - 1) * $paginate);
    }


    /**
     * Show the form for creating a new resource.
     */

    /**
     * Display the specified resource.
     */
    public function show(Receipt $receipt)
    {
        return view('receipts.show', [
            'receipt' => $receipt,
        ]);
    }

    public function print(Receipt $receipt)
    {
        $websystem = Websystem::where('slug', 'websystem')->first();
        $pdf = PDF::loadView('receipts.print', [
            'receipt' => $receipt,
            'websystem' => $websystem
        ]);
        return $pdf->download($receipt->number . str($receipt->customer_name)->slug() . '_' . str($receipt->customer_address)->slug() . '_' . str($receipt->month)->slug() . '_' . $receipt->year . '.pdf');
    }
    
    public function thermalPrint(Receipt $receipt)
    {
        $websystem = Websystem::where('slug', 'websystem')->first();

        // Atur ukuran kertas thermal (58mm x 100mm) dalam satuan milimeter
        $pdf = PDF::loadView('receipts.thermal', [
            'receipt' => $receipt,
            'websystem' => $websystem
        ])->setPaper([0, 0, 155.43, 283.5], 'portrait'); // 55mm x 100mm dalam satuan point

        //return $pdf->download($receipt->number . '_thermal_' . str($receipt->customer_name)->slug() . '_' . str($receipt->customer_address)->slug() . '_' . str($receipt->month)->slug() . '_' . $receipt->year . '.pdf');
        return $pdf->stream($receipt->number . '_thermal_' . str($receipt->customer_name)->slug() . '_' . str($receipt->customer_address)->slug() . '_' . str($receipt->month)->slug() . '_' . $receipt->year . '.pdf');
    }
}
