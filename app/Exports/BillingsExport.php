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
    protected $periode;

    public function __construct($status = null, $periode = null)
    {
        $this->status  = $status;
        $this->periode = $periode;
    }

    public function collection()
    {
        $query = Billing::with([
            'customer.user',
            'billingSubscription.customerSubscription.paket',
            'billingSubscription.customerSubscription.subscriptionDuration',
            'receipt'
        ]);

        if ($this->status && $this->status !== 'ALL') {
            $query->where('status', $this->status);
        }

        if ($this->periode) {
            $query->whereHas('billingSubscription', function ($q) {
                $q->where('billing_periode', 'like', "%{$this->periode}%");
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
            'Paket',
            'Periode',
            'Subscription',
            'Paket Price (IDR)',
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

        $status = $statusMap[$billing->status] ?? ($billing->status ?? 'UNKNOWN');

        return [
            $number++,
            $billing->billing_number,
            $billing->customer->name ?? '-',
            $billing->customer->address ?? '-',
            $billing->billingSubscription->customerSubscription->paket->name ?? '-',
            $billing->billingSubscription->billing_periode ?? '-',
            $billing->billingSubscription->customerSubscription->subscriptionDuration->name_en ?? '-',
            (int) ($billing->billingSubscription->customerSubscription->paket->price ?? 0),
            (float) round($billing->discount_amount ?? 0, 2),
            (float) round($billing->subTotal ?? 0, 2),
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

    // Buat header bold
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true], // header bold
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER, // center horizontal
                    'vertical'   => Alignment::VERTICAL_CENTER,   // middle vertical
                ],
            ],
        ];
    }
}
