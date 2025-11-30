<?php

namespace App\Exports;

use App\Helpers\FormatReceiver;
use App\Helpers\UserAuth;
use App\Models\Customer;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    private function getUserAuthData()
    {
        return UserAuth::getUserDetails();
    }

    /**
     * Ambil data dari database
     */
    public function collection()
    {
        $auth = $this->getUserAuthData();
        $user = $auth['user'];

        if ($user && $user->can('view-customers-widget-related')) {
            return Customer::where('user_id', $user->id)->get();
        }

        return Customer::get(); // tanpa withTrashed()
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
            'Price',
            'Discount (%)',
            'Discount (Nominal)',
            'Price After Discount',
            'Responsibility',
            'State',
            'Member Type',
            'Periode Discount',
        ];
    }

    /**
     * Mapping data untuk setiap baris
     */
    public function map($customer): array
    {
        static $number = 1; // Mulai nomor dari 1

        $formatter = new FormatReceiver;
        $contact = $formatter->formatReceiver($customer->contact, 'display', false);

        // Tambahkan "'" agar tetap dalam format teks di Excel
        $contact = "'".$contact;

        $nominalDiscount = optional($customer->member)->discount ?? 0;
        $packagePrice = optional($customer->package)->price ?? 0;

        $priceAfterDiscount = $customer->billing_option == 1 || ($customer->billing_option == 2 && $customer->billing_period == date('F'))
            ? $packagePrice - $nominalDiscount
            : $packagePrice;

        // Prevent division by zero
        $discountPercentage = $packagePrice > 0
            ? ($nominalDiscount / $packagePrice)  // 0.2 for 20%
            : 0;

        $activationDate = $customer->activation_date
            ? Carbon::parse($customer->activation_date)->format('d/m/Y')
            : 'No Data';

        $mikrotikName = $customer->mikrotik->name ?? '-';
        $pppTypeName = $customer->pppType->name ?? '-';
        $userName = $customer->user->name ?? '-';

        return [
            $number++,
            $customer->customer_id,
            $customer->username,
            $customer->name,
            $customer->email ?? '-',
            $contact,
            $customer->address,
            $activationDate,
            $mikrotikName,
            $customer->internet_service,
            $customer->secret_id,
            $customer->password_ppp,
            $pppTypeName,
            $customer->package->name ?? 'No Package',

            // Numeric fields
            (float) $packagePrice,
            (float) $discountPercentage,     // <-- NUMBER, NOT STRING
            (float) $nominalDiscount,
            (float) $priceAfterDiscount,

            $userName,
            $customer->activation == 'false'
                ? 'Not Activated'
                : ($customer->disabled === 'true' ? 'Disabled' : 'Enabled'),
            $customer->member->type ?? '-',
            $customer->billing_period ?? '-',
        ];
    }

    /**
     * Format angka sebagai mata uang (tanpa Rp karena sudah di-mapping)
     */
    public function columnFormats(): array
    {
        return [
            'O' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price
            'P' => NumberFormat::FORMAT_PERCENTAGE_00,           // Discount %
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Discount nominal
            'R' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price after discount
        ];
    }

    /**
     * Styling untuk header dan border seluruh tabel
     */
    public function styles(Worksheet $sheet)
    {
        // ⭐ Freeze header row
        $sheet->freezePane('A2');

        // ⭐ Auto-filter untuk seluruh header
        $sheet->setAutoFilter('A1:V1');

        // ⭐ Styling header
        $sheet->getStyle('A1:V1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '4F81BD'], // biru profesional
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // ⭐ Border untuk seluruh tabel
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // ⭐ Right align untuk kolom angka
        foreach (['O', 'P', 'Q', 'R'] as $col) {
            $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                ->getAlignment()
                ->setHorizontal('right');
        }

        // ⭐ Auto-size semua kolom
        foreach (range('A', $highestColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
