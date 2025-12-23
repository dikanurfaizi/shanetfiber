<?php

namespace App\Exports\Billing\Sheets;

use App\Models\Billings\Billing;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithMapping,
    WithHeadings,
    WithChunkReading,
    WithTitle,
    WithColumnFormatting,
    WithStyles
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BillingPeriodeSheet implements
    FromQuery,
    WithMapping,
    WithHeadings,
    WithChunkReading,
    WithTitle,
    WithColumnFormatting,
    WithStyles
{
    public function __construct(
        protected int $periodeId,
        protected string $periodeName
    ) {}

    /* ===============================
     * Sheet title (Excel tab name)
     * =============================== */
    public function title(): string
    {
        return $this->periodeName;
    }

    /* ===============================
     * Query (NO get())
     * =============================== */
    public function query()
    {
        return Billing::query()
            ->with([
                'customer:id,name,address,user_id',
                'customer.user:id,name',
                'billingItems:id,billing_id,item_type,item_name,subtotal',
                'billingPayment.paidByUser:id,name',
                'billingSubscription.billingPeriode:id,name',
            ])
            ->whereHas(
                'billingSubscription.billingPeriode',
                fn($q) => $q->where('id', $this->periodeId)
            );
    }

    /* ===============================
     * Chunk size
     * =============================== */
    public function chunkSize(): int
    {
        return 500;
    }

    /* ===============================
     * Headings
     * =============================== */
    public function headings(): array
    {
        return [
            'No',
            'Billing Number',
            'Customer',
            'Address',
            'Billing Period',
            'Package Name',
            'Package Subtotal',
            'Addons Total',
            'Discount (IDR)',
            'Discount (%)',
            'Subtotal Before Tax',
            'Tax (IDR)',
            'Grand Total',
            'Status',
            'Payment Time',
            'Teller',
            'Admin Responsible',
            'Created At'
        ];
    }

    /* ===============================
     * Mapping
     * =============================== */
    public function map($billing): array
    {
        static $no = 1;

        $packageSubtotal = 0;
        $addonsTotal     = 0;
        $discountNominal = 0;
        $packageName     = '-';

        foreach ($billing->billingItems as $item) {
            match ($item->item_type) {
                'package' => [
                    $packageSubtotal += $item->subtotal,
                    $packageName = $item->item_name,
                ],
                'addon'    => $addonsTotal += $item->subtotal,
                'discount' => $discountNominal += abs($item->subtotal),
                default    => null,
            };
        }

        $baseAmount = $packageSubtotal + $addonsTotal;

        $discountPercent = $baseAmount > 0
            ? $discountNominal / $baseAmount
            : 0;

        $statusMap = [
            'LS' => 'PAID',
            'BL' => 'UNPAID',
            'PL' => 'PAYLATER',
            'VD' => 'NOT ACTIVE',
        ];

        $formatted = $billing->created_at->format('d-m-Y');

        return [
            $no++,
            $billing->billing_number,
            $billing->customer?->name ?? '-',
            $billing->customer?->address ?? '-',
            $billing->billingSubscription?->billingPeriode?->name ?? '-',
            $packageName,

            (float) $packageSubtotal,
            (float) $addonsTotal,
            (float) $discountNominal,
            (float) $discountPercent,
            (float) $baseAmount,
            (float) ($billing->tax_amount ?? 0),
            (float) ($billing->total_amount ?? 0),

            $statusMap[$billing->status] ?? $billing->status,
            $billing->billingPayment?->payment_time
                ? Carbon::parse($billing->billingPayment->payment_time)->format('d/m/Y')
                : '-',
            $billing->billingPayment?->paidByUser?->name ?? '-',
            $billing->customer?->user?->name ?? '-',
            $formatted
        ];
    }

    /* ===============================
     * Column formatting
     * =============================== */
    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'J' => NumberFormat::FORMAT_PERCENTAGE_00,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    /* ===============================
     * Styles
     * =============================== */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');

        $sheet->getStyle('A1:R1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // ===============================
        // AUTO-SIZE ALL USED COLUMNS
        // ===============================
        $highestColumn = $sheet->getHighestColumn();
        foreach (range('A', $highestColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
