<?php

namespace App\Http\Controllers\Billing;

use App\Models\Billing\Billing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BillingHistoryController extends Controller
{
    public function index(Request $request)
    {
        $billings = Billing::with(['customer', 'receipt'])
            ->orderBy('due_date', 'desc')
            ->paginate(20);

        return view('billingHistory.index', compact('billings'));
    }
}
