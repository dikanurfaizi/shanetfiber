<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles
{
    /**
     * Ambil data dari database
     */
    public function collection()
    {
        return Customer::all();
    }

    /**
     * Header untuk file Excel
     */
    public function headings(): array
    {
        return [
            'No',
            'Customer ID',
            'Username',
            'Name',
            'Email',
            'Contact',
            'Address',
            'Activation Date',
            'Mikrotik Name',
            'Internet Service',
            'Secret ID',
            'Password PPP',
            'PPP Type ID',
            'Package',
            'Bill',
            'Discount',
            'Responsibility',
            'State',
            'Member',
            'Discount',
            'Periode Discount'
        ];
    }

    /**
     * Mapping data untuk setiap baris
     */
    public function map($customer): array
    {
        static $number = 1; // Mulai nomor dari 1

        $contact = $customer->contact;

        // Ubah format dari 628xxxx ke 08xxxx
        if (preg_match('/^628/', $contact)) {
            $contact = preg_replace('/^628/', '08', $contact);
        }

        // Tambahkan "'" agar tetap dalam format teks di Excel
        $contact = "'" . $contact;

        $price = $customer->billing_option == 1 || ($customer->billing_option == 2 && $customer->billing_period == date('F'))
            ? optional($customer->paket)->price - optional($customer->member)->discount
            : optional($customer->paket)->price;

        $activationDate = $customer->activation_date
            ? Carbon::parse($customer->activation_date)->format('d-m-Y')
            : 'No Data';

        return [
            $number++, // Auto numbering
            $customer->customer_id,
            $customer->username,
            $customer->name,
            $customer->email ?? '-',
            $contact,
            $customer->address,
            $activationDate,
            $customer->mikrotik->name,
            $customer->internet_service,
            $customer->secret_id,
            $customer->password_ppp,
            $customer->ppp_type->name,
            $customer->paket->name ?? 'No Package',
            'Rp ' . number_format($price, 0, ',', '.'), // Format Rupiah manual
            $customer->discount . ' %',
            $customer->user->name ?? 'No Linked User',
            $customer->activation == 'false' ? 'Not Activated' : ($customer->disabled === 'true' ? 'Disabled' : 'Enabled'),
            $customer->member->type ?? '-',
            'Rp ' . number_format($customer->member->discount ?? 0, 0, ',', '.'), // Format Rp
            $customer->billing_period ?? '-'
        ];
    }

    /**
     * Format angka sebagai mata uang (tanpa Rp karena sudah di-mapping)
     */
    public function columnFormats(): array
    {
        return [
            'O' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Format angka ribuan (Bill)
            'T' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Format angka ribuan (Discount Amount)
        ];
    }

    /**
     * Styling untuk header dan border seluruh tabel
     */
    public function styles(Worksheet $sheet)
    {
        // Bold dan Center untuk Header (baris 1)
        $sheet->getStyle('A1:U1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center'],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ]
        ]);

        // Border untuk semua sel yang ada datanya
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ]
        ]);

        // **Atur lebar kolom secara otomatis mengikuti isi terpanjang**
        foreach (range('A', $highestColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
