<?php

namespace App\Exports;

use App\Models\Receipt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;
use App\Helpers\UserAuth;

class ReceiptsExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles
{
    private function getUserAuthData()
    {
        return UserAuth::getUserDetails();
    }

    public function collection()
    {
        $auth = $this->getUserAuthData();
        $user = $auth['user'];

        if ($user && $user->can('view-customers-widget-related')) {
            return Receipt::withTrashed()
                ->with(['customer' => function ($query) {
                    $query->withTrashed();
                }])
                ->whereRelation('customer', 'user_id', $user->id)
                ->get();
        }

        return Receipt::withTrashed() // jika pakai SoftDeletes
            ->with(['customer' => function ($query) {
                $query->withTrashed(); // jika customers juga pakai SoftDeletes
            }])
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Receipt Number',
            'Billing Number',
            'Periode',
            'Customer ID',
            'Customer Name',
            'Paket Name',
            'Paket Price',
            'Tax',
            'Discount',
            'Price After Discount',
            'Payment Method',
            'Bank Name',
            'Account Name',
            'Account Number',
            'Payment Time',
            'Created By',
        ];
    }

    public function map($receipt): array
    {
        static $no = 1;

        $isTransfer = strtolower($receipt->payment_methode) === 'transfer';

        return [
            $no++,
            $receipt->number,
            $receipt->billing_number,
            $receipt->periode,
            $receipt->customer->customer_id ?? '-',
            $receipt->customer_name,
            $receipt->paket_name,
            $receipt->paket_price,
            is_null($receipt->tax) ? 0 : $receipt->tax,
            is_null($receipt->discount) ? 0 : $receipt->discount,
            is_null($receipt->price_after_discount) ? 0 : $receipt->price_after_discount,
            match (strtolower($receipt->payment_methode)) {
                'cash' => 'Cash',
                'transfer' => 'Transfer',
                null, '' => '-',
                default => $receipt->payment_methode,
            },
            $isTransfer ? $receipt->bank_name ?? '-' : '-',          // Bank Name
            $isTransfer ? $receipt->account_name ?? '-' : '-',       // Account Name
            $isTransfer ? ("'" . ($receipt->account_number ?? '-')) : '-',     // Account Number
            $receipt->payment_time ? Carbon::parse($receipt->payment_time)->format('d-m-Y H:i:s') : '-',
            $receipt->created_name,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_NUMBER, // Paket Price
            'I' => NumberFormat::FORMAT_NUMBER, // Tax
            'J' => NumberFormat::FORMAT_NUMBER, // Discount
            'K' => NumberFormat::FORMAT_NUMBER, // Price After Discount
            'O' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center'],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        foreach (range('A', $highestColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
