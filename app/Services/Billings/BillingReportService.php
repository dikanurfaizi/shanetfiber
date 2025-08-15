<?php

namespace App\Services\Billings;

use App\Models\Bank;
use App\Models\Customer;
use App\Models\User;
use App\Models\Billing\BillingPeriode;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\LogHelper;

class BillingReportService
{
    public function getFilterData(?string $traceId): array
    {
        $startTime = microtime(true);

        try {
            $filterData = [
                'bank' => Bank::orderBy('name')
                    ->where('disabled', 'false')
                    ->pluck('name', 'id'),
                'address' => Customer::whereNotNull('address')
                    ->pluck('address', 'address'),
                'billingsPeriode' => BillingPeriode::all(),
                'months' => BillingPeriode::pluck('month', 'month'),
                'years' => BillingPeriode::pluck('year', 'year'),
                'userAdmin' => $this->getUserAdminMapping(),
                'tellers' => User::pluck('name', 'name'),
            ];

            LogHelper::logInfo(
                null,
                [
                    'filters_count' => array_map(fn($v) => is_countable($v) ? count($v) : null, $filterData),
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
                ],
                200,
                "Retrieved filter data for billing report.",
                $traceId
            );

            return $filterData;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
                ],
                500,
                "Failed to retrieve filter data for billing report.",
                $traceId
            );
            throw $e;
        }
    }

    private function getUserAdminMapping(): array
    {
        // Hindari N+1 query dengan eager load user langsung
        $customers = Customer::with('user')
            ->orderBy('user_id')
            ->get();

        return $customers->mapWithKeys(function ($customer) {
            if ($customer->user) {
                return [$customer->user->id => $customer->user->name];
            }
            return [0 => 'Not Linked User'];
        })->toArray();
    }

    public function generatePdf($query, array $searchParams, string $traceId = null)
    {
        $startTime = microtime(true);

        try {
            $dataPrint = $query->get();

            $pdf = PDF::loadView('print.billing', [
                'dataPrint' => $dataPrint,
                'search' => [
                    'searchMonth' => $searchParams['month'] ?? null,
                    'searchYear' => $searchParams['year'] ?? null,
                    'searchStatus' => $searchParams['status'] ?? null,
                    'searchName' => $searchParams['name'] ?? null,
                    'searchAddress' => $searchParams['address'] ?? null
                ]
            ]);

            LogHelper::logInfo(
                null,
                [
                    'records_count' => $dataPrint->count(),
                    'search_params' => $searchParams,
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
                ],
                200,
                "Generated billing report PDF.",
                $traceId
            );

            return $pdf;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'search_params' => $searchParams,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
                ],
                500,
                "Failed to generate billing report PDF.",
                $traceId
            );
            throw $e;
        }
    }
}
