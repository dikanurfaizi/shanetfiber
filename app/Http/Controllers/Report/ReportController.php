<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Reports\ReportService;
use App\Models\User;
use App\Helpers\UserAuth;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Ambil KPI untuk semua user aktif.
     */
    public function getAllKPI()
    {
        // Ambil semua user yang tidak disabled
        $users = User::where('disabled', false)->get();

        // Panggil service per user
        $data = $users->map(fn($u) => $this->reportService->monthly($u->id));

        return response()->json($data);
    }

    /**
     * Ambil KPI untuk satu user berdasarkan ID.
     */
    public function getKPIbyUserId($userId)
    {
        $data = $this->reportService->monthly($userId);
        return response()->json($data);
    }
}