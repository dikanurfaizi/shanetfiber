<?php

namespace App\Services\Receipts;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPayment;
use App\Models\Customers\CustomerSubscription;
use App\Models\Membership\CustomerMembership;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateReceiptService
{
    public function createReceipt(
        CustomerSubscription $customerSubscription,
        Billing $billing,
        BillingPayment $billingPayment,
        array $paymentData,
        string $traceId,
        ?string $createdBy = null
    ): array {
        $timer = new ExecutionTimer();
        try {
            return DB::transaction(function () use (
                $customerSubscription,
                $billing,
                $billingPayment,
                $traceId,
                $paymentData,
                &$timer
            ) {

                /** -----------------------------------------
                 * 1. Tentukan due date
                 * ----------------------------------------- */
                $dueDate = $billing->status === 'PL'
                    ? $billing->paylater_until
                    : $billing->due_date;

                /** -----------------------------------------
                 * 2. Ambil membership customer (FIXED LOGIC)
                 * ----------------------------------------- */
                $customerMembership = CustomerMembership::with('membership')
                    ->where('customer_id', $customerSubscription->customer_id)
                    ->where('start_date', '<', $dueDate)
                    ->where(function ($q) use ($dueDate) {
                        $q->whereNull('end_date')
                            ->orWhere('end_date', '<', $dueDate);
                    })
                    ->first();

                /** -----------------------------------------
                 * 3. Ambil billing periode
                 * ----------------------------------------- */
                $periode = $billing->billingSubscription->billingPeriode;
                $month   = $periode->month;
                $year    = $periode->year;

                /** -----------------------------------------
                 * 4. Ambil receipt terakhir (LOCKED)
                 * ----------------------------------------- */
                $lastReceiptNumber = Receipt::query()
                    ->where('number', 'like', "KWT-%-{$month}-{$year}")
                    ->lockForUpdate()
                    ->orderByDesc('number')
                    ->value('number');

                /** -----------------------------------------
                 * 5. Hitung sequence (WITH FALLBACK)
                 * ----------------------------------------- */
                $sequence = 1;

                if ($lastReceiptNumber) {
                    $pattern = sprintf(
                        '/^KWT-(\d{5})-%s-%s$/',
                        preg_quote($month, '/'),
                        preg_quote($year, '/')
                    );

                    if (preg_match($pattern, $lastReceiptNumber, $matches)) {
                        $sequence = ((int) $matches[1]) + 1;
                    }
                }

                /** -----------------------------------------
                 * 6. Generate receipt number
                 * ----------------------------------------- */
                $receiptNumber = sprintf(
                    'KWT-%05d-%s-%s',
                    $sequence,
                    $month,
                    $year
                );

                $receiptData = [
                    'billing_payment_id'   => $billingPayment->id,
                    'slug'                 => str(
                        'inv-' . $customerSubscription->customer_id . '-' . Str::random(4) . '-' . Str::random(16)
                    )->slug(),
                    'periode'              => $periode->name,
                    'number'               => $receiptNumber,
                    'billing_number'       => $billing->billing_number,
                    'customer_id'          => $customerSubscription->customer_id,
                    'customer_name'        => $customerSubscription->customer->name,
                    'customer_address'     => $customerSubscription->customer->address,
                    'package_name'         => $customerSubscription->package->name,
                    'package_price'        => $customerSubscription->package->price,
                    'tax'                  => $billing->tax_amount,
                    'payment_methode'      => $billingPayment->payment_method,
                    'payment_time'         => $billingPayment->payment_time,
                    'reference_id'         => $billingPayment->reference_id,
                    'discount'             => $billing->discount_amount,
                    'price_after_discount' => $billing->total_amount,
                    'member_type'          => $customerMembership?->membership->name ?? 'NON_MEMBER',
                    'created_name'         => $createdBy ?? auth()->user()->name ?? 'SYSTEM',
                    'teller_name'          => $createdBy ?? auth()->user()->name ?? 'SYSTEM',
                ];

                if ($paymentData['payment_method'] === 'transfer') {
                    $receiptData['bank_name'] = $paymentData['bank_name'];
                    $receiptData['account_name'] = $paymentData['account_name'];
                    $receiptData['account_number'] = $paymentData['account_number'];
                    $receiptData['deposit_bank_name'] = $paymentData['deposit_bank_name'];
                    $receiptData['deposit_account_name'] = $paymentData['deposit_account_name'];
                    $receiptData['deposit_account_number'] = $paymentData['deposit_account_number'];
                    $receiptData['reference_id'] = $paymentData['reference_id'] ? $paymentData['reference_id'] : $billingPayment->reference_id;
                }

                /** -----------------------------------------
                 * 7. Insert receipt
                 * ----------------------------------------- */
                $receipt = Receipt::create($receiptData);

                foreach ($billing->billingItems as $addon) {
                    ReceiptItem::create([
                        'receipt_id' => $receipt->id,
                        'type' => $addon->item_type,
                        'ref_id' => $addon->source_id,
                        'name' => $addon->item_name,
                        'qty' => $addon->qty,
                        'unit_price' => $addon->price,
                        'subtotal' => $addon->subtotal,
                    ]);
                }


                /** -----------------------------------------
                 * 8. Logging
                 * ----------------------------------------- */
                LogHelper::logInfo(
                    null,
                    [
                        'customer_id' => $customerSubscription->customer_id,
                        'receipt_id'  => $receipt->id,
                        'number'      => $receipt->number,
                    ],
                    200,
                    'Successfully created receipt',
                    $traceId,
                    $timer->lap()
                );

                return [
                    'success' => true,
                    'receipt' => $receipt,
                    'membership' => $customerMembership
                ];
            });
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ],
                    'customer_id' => $customerSubscription->customer_id,
                    'billing_id'  => $billing->id,
                ],
                500,
                'Failed to create receipt',
                $traceId,
                $timer->lap()
            );

            throw $e; // rollback otomatis
        }
    }
}
