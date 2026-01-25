<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\KpiReportService;
use Illuminate\Http\Request;

class KpiController extends Controller
{
    public function index(Request $request, KpiReportService $service)
    {
        $year = $request->get('year', now()->year);

        $data = $service->buildKpiByMikrotik($year);

        return view('reports.kpi.index', [
            'year' => $year,
            'mikrotiks' => $data,
        ]);
    }
}
