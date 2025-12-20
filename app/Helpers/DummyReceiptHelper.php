<?php

namespace App\Helpers;

use App\DataTransferObjects\WhatsappJobDto;
use App\Models\Billings\Billing;
use App\Models\Receipt;
use Illuminate\Support\Carbon;
use App\Helpers\LogHelper;

class DummyReceiptHelper
{
    private function baseData(Billing $billing, ?Carbon $payLaterDate = null, string $action): array
    {
        $baseData = [
            'billing_number' => $billing->billing_number,
            'customer_id' => $billing->customer_id,
            'customer_name' => $billing->customer_name,
            'package_name' => $billing->package?->name ?? 'Unknown Package',
            'package_price' => $billing->total_amount,
            'payment_time' => now(),
            'payment_methode' => $action,
            'created_name' => 'system',
            'teller_name' => 'system',
            'is_dummy' => true,
        ];

        if ($action === 'paylater' && $payLaterDate !== null) {
            $baseData['pay_later_date'] = $payLaterDate;
        }

        return $baseData;
    }

    private function fallbackData(Billing $billing, string $action): array
    {
        $fallbackData = [
            'billing_number' => $billing->billing_number,
            'customer_id' => $billing->customer_id,
            'customer_name' => $billing->customer_name,
            'payment_time' => now(),
            'payment_methode' => $action,
            'is_dummy' => true,
        ];

        return $fallbackData;
    }

    /**
     * Create dummy receipt model (not persisted)
     *
     * @throws \Throwable
     */
    public function createPaylater(
        Billing $billing,
        string $action = 'paylater',
        ?Carbon $payLaterDate = null,
        string $traceId
    ): Receipt {
        try {
            $baseData = $this->baseData($billing, $payLaterDate, $action);
            return (new Receipt())->newInstance($baseData, true);
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'error' => $e->getMessage(),
                'trace_id' => $traceId,
            ], 500, "Failed to create dummy receipt for {$action}");
            $fallbackData = $this->fallbackData($billing, $action);
            return (new Receipt())->newInstance($fallbackData, true);
        }
    }

    /**
     * Create dummy receipt model (not persisted)
     *
     * @throws \Throwable
     */
    public function createUnpayment(
        int $billingId,
        string $action = 'unpayment',
        ?int $receiptId,
        ?array $receiptData,
        string $traceId
    ): ?Receipt {
        try {
            // 1️⃣ Dari payload langsung
            if (!empty($receiptData)) {
                return (new Receipt())->newInstance($receiptData, true);
            }

            // 2️⃣ Dari ID receipt
            if ($receiptId) {
                $found = Receipt::withTrashed()->find($receiptId);
                if ($found) return $found;
            }

            // 3️⃣ Ambil billing dengan relasi subscription
            $billing = Billing::with(['billingSubscription'])->find($billingId);
            if (!$billing) {
                LogHelper::logWarning(null, [
                    'billing_id' => $billingId,
                ], 404, "Billing not found when resolving unpayment", $traceId);
                return null;
            }

            // 4️⃣ Cari receipt berdasarkan customer_id dan periode (dengan OR format)
            if (!$billing->receipt && $billing->customer_id) {
                [$shortPeriode, $longPeriode] = $this->normalizePeriodeVariants(
                    $billing->billingSubscription?->billing_periode
                );

                $found = Receipt::withTrashed()
                    ->where('customer_id', $billing->customer_id)
                    ->where(function ($q) use ($shortPeriode, $longPeriode) {
                        if ($shortPeriode && $longPeriode) {
                            $q->where('periode', $shortPeriode)
                                ->orWhere('periode', $longPeriode);
                        } elseif ($shortPeriode) {
                            $q->where('periode', $shortPeriode);
                        } elseif ($longPeriode) {
                            $q->where('periode', $longPeriode);
                        }
                    })
                    ->latest('created_at')
                    ->first();

                if ($found) {
                    $billing->setRelation('receipt', $found);
                    return $found;
                }
            }

            // 5️⃣ Fallback dummy jika semua gagal
            $fallbackData = $this->fallbackData($billing, $action);
            LogHelper::logWarning(null, [
                'billing_id' => $billing->id ?? null,
                'customer_id' => $billing->customer_id ?? null,
            ], 200, 'No matching receipt found, using dummy', $traceId);

            return (new Receipt())->newInstance($fallbackData, true);
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'error_code'    => $e->getCode(),
                'error_message' => $e->getMessage(),
                'action'        => $action,
            ], 501, "Failed to resolve receipt for unpayment", $traceId);

            $fallbackData = $this->fallbackData($billing ?? new Billing(), $action);
            return (new Receipt())->newInstance($fallbackData, true);
        }
    }

    private function normalizePeriodeVariants(?string $periode): array
    {
        if (empty($periode)) return [null, null];

        try {
            $carbon = Carbon::parse('1 ' . $periode);
            return [
                $carbon->format('M-y'),     // "Oct-25"
                $carbon->format('F Y'),     // "October 2025"
            ];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }
}
