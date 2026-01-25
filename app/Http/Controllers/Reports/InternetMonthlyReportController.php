<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\MonthlyInternetReportService;
use Illuminate\Http\Request;

class InternetMonthlyReportController extends Controller
{
    public function index(Request $request, MonthlyInternetReportService $service)
    {
        // $request->user_id = 1; // temporary hardcode user id to 1
        // $request->billing_periode_id = 1; // temporary hardcode billing periode id to 1
        // $request->mikrotik_id = null; // temporary hardcode mikrotik id to null

        // $request->validate([
        //     'billing_periode_id' => ['required', 'integer'],
        //     'user_id' => ['required', 'integer'],
        //     'mikrotik_id' => ['nullable', 'integer'],
        // ]);

        $user_id = $request->user_id ?? auth()->user()->id;
        $billing_periode_id = '33'; // Ganti dengan ID periode penagihan yang diinginkan
        $mikrotik_id = $request->mikrotik_id ?? 1; // Ganti dengan ID Mikrotik yang diinginkan atau null   

        $data = $service->generate(
            userId: (int) $user_id,
            billingPeriodeId: (int) $billing_periode_id,
            mikrotikId: $mikrotik_id ? (int) $mikrotik_id : null,
        );

        return view('reports.index', $data);
    }

    public function show($id)
    {
        //
    }
}
