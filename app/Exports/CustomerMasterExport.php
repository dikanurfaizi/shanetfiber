<?php

namespace App\Exports;

use App\Helpers\FormatReceiver;
use App\Helpers\UserAuth;
use App\Models\Customers\Customer;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CustomerMasterExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithColumnFormatting,
    WithStyles
{
    public function collection()
    {
        $auth = UserAuth::getUserDetails();
        $user = $auth['user'];

        $query = Customer::with([
            'user',
            'subscriptions.package',
            'customerMembership.membership',
        ]);

        // 🔒 scope sesuai permission lama kamu
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
            'Responsibility',
            'Status',
            'Active Package',
            'Package Price',
            'Membership',
            'Membership Period',
            'Billing Option',
        ];
    }

    public function map($customer): array
    {
        static $no = 1;

        $formatter = new FormatReceiver();
        $contact = "'" . $formatter->formatReceiver(
            $customer->contact,
            'display',
            false
        );

        // ===== Status =====
        $status = $customer->activation === 'false'
            ? 'Not Activated'
            : ($customer->disabled === 'true' ? 'Disabled' : 'Enabled');

        // ===== Subscription =====
        $subscription = $customer->subscriptions->last();
        $packageName = $subscription?->package?->name ?? '-';
        $packagePrice = (float) ($subscription?->package?->price ?? 0);

        // ===== Membership =====
        $membershipName = '-';
        $membershipPeriod = '-';

        if ($customer->customerMembership) {
            $membership = $customer->customerMembership;
            $membershipName = $membership->membership?->name ?? '-';

            if ($membership->end_date) {
                $membershipPeriod = Carbon::parse(
                    $membership->end_date
                )->format('F Y');
            } else {
                $membershipPeriod = 'Monthly';
            }
        }

        return [
            $no++,
            $customer->customer_id,
            $customer->username,
            $customer->name,
            $customer->email ?? '-',
            $contact,
            $customer->address,
            $customer->activation_date
                ? Carbon::parse($customer->activation_date)->format('d/m/Y')
                : '-',
            $customer->user?->name ?? '-',
            $status,
            $packageName,
            $packagePrice,
            $membershipName,
            $membershipPeriod,
            $customer->billing_option ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Package Price
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:O1');

        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);

        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
