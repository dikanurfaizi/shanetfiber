<?php

namespace App\Exports;

use App\Helpers\FormatReceiver;
use App\Helpers\UserAuth;
use App\Models\Customers\Customer;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithColumnFormatting,
    WithStyles
{
    private function getUserAuthData()
    {
        return UserAuth::getUserDetails();
    }

    public function collection()
    {
        $auth = $this->getUserAuthData();
        $user = $auth['user'];

        $query = Customer::query()
            ->with([
                'activeSubscription.package',
                'activeMembership.membership',
                'mikrotik',
                'pppType',
                'user',
            ]);

        if ($user && $user->can('view-customers-widget-related')) {
            $query->where('user_id', $user->id);
        }

        return $query->get();
    }

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
            'PPP Type',
            'Package',
            'Package Price',
            'Discount (%)',
            'Discount (Nominal)',
            'Price After Discount',
            'Responsibility',
            'State',
            'Membership',
            'Discount Period',
        ];
    }

    public function map($customer): array
    {
        static $no = 1;

        $formatter = new FormatReceiver;
        $contact = "'" . $formatter->formatReceiver(
            $customer->contact,
            'display',
            false
        );

        // =============================
        // ACTIVE SUBSCRIPTION
        // =============================
        $subscription = $customer->activeSubscription;
        $package      = $subscription?->package;

        $packageName  = $package?->name ?? '-';
        $packagePrice = (float) ($package?->price ?? 0);

        // =============================
        // ACTIVE MEMBERSHIP
        // =============================
        $custMembership = $customer->activeMembership;
        $membership     = $custMembership?->membership;

        $membershipName = $membership?->name ?? '-';
        $discountNominal = (float) ($membership?->discount_value ?? 0);

        $discountPercent = $packagePrice > 0
            ? $discountNominal / $packagePrice
            : 0;

        $priceAfterDiscount = max(
            $packagePrice - $discountNominal,
            0
        );

        // =============================
        // DISCOUNT PERIOD
        // =============================
        if (!$custMembership) {
            $discountPeriod = '-';
        } elseif ($custMembership->end_date === null) {
            $discountPeriod = 'Monthly';
        } else {
            $discountPeriod = Carbon::parse(
                $custMembership->end_date
            )->format('F Y');
        }

        // =============================
        // OTHERS
        // =============================
        $activationDate = $customer->activation_date
            ? Carbon::parse($customer->activation_date)->format('d/m/Y')
            : '-';

        $state = $customer->activation === 'false'
            ? 'Not Activated'
            : ($customer->disabled === 'true'
                ? 'Disabled'
                : 'Enabled');

        return [
            $no++,
            $customer->customer_id,
            $customer->username,
            $customer->name,
            $customer->email ?? '-',
            $contact,
            $customer->address,
            $activationDate,
            $customer->mikrotik->name ?? '-',
            $customer->internet_service,
            $customer->secret_id,
            $customer->password_ppp,
            $customer->pppType->name ?? '-',
            $packageName,

            // numeric
            $packagePrice,
            $discountPercent,
            $discountNominal,
            $priceAfterDiscount,

            $customer->user->name ?? '-',
            $state,
            $membershipName,
            $discountPeriod,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'O' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'P' => NumberFormat::FORMAT_PERCENTAGE_00,
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'R' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:V1');

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
                'fillType' => 'solid',
                'color' => ['rgb' => '4F81BD'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                ],
            ],
        ]);

        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$highestCol}{$highestRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle('thin');

        foreach (['O', 'P', 'Q', 'R'] as $col) {
            $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                ->getAlignment()
                ->setHorizontal('right');
        }

        foreach (range('A', $highestCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
