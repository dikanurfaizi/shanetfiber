<?php

namespace App\Services\Mikrotik;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\AutoIsolir;
use App\Models\Billings\Billing;
use App\Models\Customers\Customer;
use Illuminate\Support\Carbon;

class MikrotikComment
{
    /**
     * Konteks 1: Komentar saat Aktivasi Pelanggan Pertama Kali
     */
    public function getCommentForActivation(Customer $customer, string $traceId): string
    {
        $timer = new ExecutionTimer();

        try {
            $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            if (!$autoIsolir) {
                return 'create_from_customer_management';
            }

            $activationDay = $customer->activation_date
                ? Carbon::parse($customer->activation_date)->format('d')
                : now()->format('d');

            return $this->buildCommentString(
                $autoIsolir,
                $activationDay,
                now()->addMonth()->format('m_Y')
            );
        } catch (\Throwable $e) {
            LogHelper::logError(null, ['customer_id' => $customer->id, 'error' => $e->getMessage()], 500, '[MikrotikComment] Failed activation comment', $traceId, $timer->lap());

            return 'create_from_customer_management';
        }
    }

    /**
     * Konteks 2: Komentar saat Pembayaran Tagihan Bulanan (Payment)
     */
    public function getCommentForPayment(Billing $billing, string $traceId): string
    {
        $timer = new ExecutionTimer();

        try {
            $customer = $billing->customer;
            $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            if (!$autoIsolir) {
                return 'create_from_customer_management';
            }

            $billingSubPeriode = $billing->billingSubscriptionPeriode;
            $periodeName = $billingSubPeriode?->billingPeriode?->name;

            $baseDate = $periodeName ? Carbon::parse($periodeName) : now();
            $activationDay = $customer->activation_date
                ? Carbon::parse($customer->activation_date)->format('d')
                : $baseDate->format('d');

            return $this->buildCommentString(
                $autoIsolir,
                $activationDay,
                $baseDate->copy()->addMonth()->format('m_Y')
            );
        } catch (\Throwable $e) {
            LogHelper::logError(null, ['billing_id' => $billing->id, 'error' => $e->getMessage()], 500, '[MikrotikComment] Failed payment comment', $traceId, $timer->lap());

            return 'create_from_customer_management';
        }
    }

    /**
     * Konteks 3: Komentar khusus saat Status Expired / Not In Period / Void
     */
    public function getCommentForExpired(Customer $customer, string $traceId): string
    {
        $timer = new ExecutionTimer();

        try {
            $customer->loadMissing('mikrotik');
            $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            if (!$autoIsolir) {
                return 'create_from_customer_management';
            }

            $activationDay = $customer->activation_date
                ? Carbon::parse($customer->activation_date)->format('d')
                : now()->format('d');

            return $this->buildCommentString(
                $autoIsolir,
                $activationDay,
                now()->addMonth()->format('m_Y')
            );
        } catch (\Throwable $e) {
            LogHelper::logError(null, ['customer_id' => $customer->id, 'error' => $e->getMessage()], 500, '[MikrotikComment] Failed expired comment', $traceId, $timer->lap());

            return 'create_from_customer_management';
        }
    }

    /**
     * Helper terpusat untuk menyusun string format komentar
     */
    private function buildCommentString(AutoIsolir $autoIsolir, string $day, string $nextMonthFormatted): string
    {
        $isActivationDateActive = filter_var($autoIsolir->activation_date, FILTER_VALIDATE_BOOLEAN);

        if ($isActivationDateActive) {
            $nextMonthDate = Carbon::createFromFormat('m_Y', $nextMonthFormatted);
            $maxDays = $nextMonthDate->daysInMonth;
            $numericDay = (int) $day;

            if ($numericDay > $maxDays) {
                $numericDay = $maxDays;
            }

            $formattedDay = str_pad((string) $numericDay, 2, '0', STR_PAD_LEFT);

            return $autoIsolir->comment_unpayment . '_' . $formattedDay . '_' . $nextMonthFormatted;
        }

        return $autoIsolir->comment_payment ?? 'create_from_customer_management';
    }
}
