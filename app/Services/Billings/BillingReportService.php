<?php

namespace App\Services\Billings;

use App\Models\Bank;
use App\Models\Customer;
use App\Models\User;
use App\Models\Billing\BillingPeriode;
use Barryvdh\DomPDF\Facade\Pdf;

class BillingReportService
{
    public function getFilterData()
    {
        return [
            'bank' => Bank::orderBy('name')->where('disabled', 'false')->pluck('name', 'id'),
            'address' => Customer::whereNotNull('address')->pluck('address', 'address'),
            'billingsPeriode' => BillingPeriode::all(),
            'months' => BillingPeriode::pluck('month', 'month'),
            'years' => BillingPeriode::pluck('year', 'year'),
            'userAdmin' => $this->getUserAdminMapping(),
            'tellers' => User::pluck('name', 'name'),
        ];
    }

    private function getUserAdminMapping()
    {
        return Customer::orderBy('user_id')
            ->get()
            ->mapWithKeys(function ($customer) {
                if ($customer->user_id) {
                    $user = User::find($customer->user_id);
                    return [$user->id => $user->name];
                }
                return [0 => 'Not Linked User'];
            });
    }

    public function generatePdf($query, array $searchParams)
    {
        $dataPrint = $query->get();

        return PDF::loadView('print.billing', [
            'dataPrint' => $dataPrint,
            'search' => [
                'searchMonth' => $searchParams['month'],
                'searchYear' => $searchParams['year'],
                'searchStatus' => $searchParams['status'],
                'searchName' => $searchParams['name'],
                'searchAddress' => $searchParams['address']
            ]
        ]);
    }
}
