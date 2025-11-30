<?php

namespace App\Exports;

use App\Models\Billing\Billing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Carbon;

class BillingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $status;
    protected $periodeId;
    protected $periode;

    public function __construct($status = null, $periodeId = null, $periode = null)
    {
        $this->status    = $status;
        $this->periodeId = $periodeId;
        $this->periode = $periode;
    }

    public function collection()
    {
        $query = Billing::with([
            'customer.user',
            'billingSubscription.customerSubscription.package',
            'billingSubscription.customerSubscription.subscriptionDuration',
            'billingSubscription',
            'billingSubscription.billingPeriode',
            'receipt'
        ]);

        // Filter status (pastikan uppercase biar konsisten)
        if (!empty($this->status) && strtoupper($this->status) !== 'ALL') {
            $query->where('status', strtoupper(trim($this->status)));
        }

        // Filter periode (hanya kalau benar-benar ada nilai numerik)
        if (!empty($this->periodeId) && is_numeric($this->periodeId)) {
            $query->whereHas('billingSubscription.billingPeriode', function ($q) {
                $q->where('id', $this->periodeId);
            });
        }

        if (!empty($this->periode)) {
            $query->whereHas('billingSubscription.billingPeriode', function ($q) {
                $q->where('name', $this->periode);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Billing Number',
            'Customer',
            'Address',
            'Package',
            'Periode',
            'Subscription',
            'Package Price (IDR)',
            'Discount (IDR)',
            'SubTotal (IDR)',
            'Tax (IDR)',
            'Total (IDR)',
            'Created At',
            'Created By',
            'Teller Name',
            'Status',
            'Payment Time',
            'Pay Later Until',
            'Admin Responsible'
        ];
    }

    public function map($billing): array
    {
        static $number = 1;

        $statusMap = [
            'LS' => 'PAID',
            'BL' => 'UNPAID',
            'PL' => 'PAYLATER',
            'NP' => 'NOT ACTIVE PERIOD',
        ];

        $status = $statusMap[$billing->status] ?? strtoupper($billing->status ?? 'UNKNOWN');

        return [
            $number++,
            $billing->billing_number,
            $billing->customer->name ?? '-',
            $billing->customer->address ?? '-',
            $billing->billingSubscription->customerSubscription->package->name ?? '-',
            $billing->billingSubscription->billingPeriode->name ?? '-', // ambil nama periode
            $billing->billingSubscription->customerSubscription->subscriptionDuration->name_en ?? '-',
            (float) ($billing->billingSubscription->customerSubscription->package->price ?? 0),
            (float) round($billing->discount_amount ?? 0, 2),
            (float) round($billing->subtotal ?? 0, 2),
            (float) round($billing->tax_amount ?? 0, 2),
            (float) round($billing->total_amount ?? 0, 2),
            $billing->created_at?->format('d/m/Y') ?? '-',
            $billing->created_by ?? '-',
            $billing->teller_name ?? '-',
            $status,
            $billing->payment_time
                ? Carbon::parse($billing->payment_time)->format('d/m/Y')
                : '-',
            $billing->pay_later
                ? Carbon::parse($billing->pay_later)->format('d/m/Y')
                : '-',
            $billing->customer?->user?->name ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
