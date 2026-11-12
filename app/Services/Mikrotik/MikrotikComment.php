<?php

namespace App\Services\Mikrotik;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\AutoIsolir;
use App\Models\Billings\Billing;
use App\Models\Customers\Customer;

class MikrotikComment
{
    private string $fallbackPaymentComment = 'payment';
    private string $fallbackUnpaymentComment = 'unpayment';

    /**
     * Konteks 1: Komentar saat Aktivasi Pelanggan Pertama Kali
     */
    public function getCommentForActivation(Customer $customer, string $traceId): string
    {
        $timer = new ExecutionTimer();

        try {
            $autoIsolir = $this->getAutoIsolir($customer->mikrotik_id, ['customer_id' => $customer->id], 'activation', $traceId, $timer);

            if (!$autoIsolir) {
                return $this->fallbackUnpaymentComment;
            }

            $isActivation = $autoIsolir->activation_date ?? false;

            $comment = $this->buildCommentString($autoIsolir, $isActivation);

            LogHelper::logInfo('MikrotikComment', [
                'customer_id' => $customer->id,
                'comment'     => $comment,
            ], 200, 'Activation comment generated successfully', $traceId, $timer->lap());

            return $comment;
        } catch (\Throwable $e) {
            LogHelper::logError('MikrotikComment', [
                'customer_id' => $customer->id,
                'error'       => $e->getMessage()
            ], 500, 'Failed activation comment', $traceId, $timer->lap());

            return $this->fallbackUnpaymentComment;
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
            $autoIsolir = $this->getAutoIsolir(
                $customer?->mikrotik_id,
                ['billing_id' => $billing->id, 'customer_id' => $customer?->id],
                'payment',
                $traceId,
                $timer
            );

            if (!$autoIsolir) {
                return $this->fallbackPaymentComment;
            }

            $comment = $this->buildCommentString($autoIsolir, true);

            LogHelper::logInfo('MikrotikComment', [
                'billing_id'  => $billing->id,
                'customer_id' => $customer?->id,
                'comment'     => $comment,
            ], 200, 'Payment comment generated successfully', $traceId, $timer->lap());

            return $comment;
        } catch (\Throwable $e) {
            LogHelper::logError('MikrotikComment', [
                'billing_id' => $billing->id,
                'error'      => $e->getMessage()
            ], 500, 'Failed payment comment', $traceId, $timer->lap());

            return $this->fallbackPaymentComment;
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
            $autoIsolir = $this->getAutoIsolir($customer->mikrotik_id, ['customer_id' => $customer->id], 'expired', $traceId, $timer);

            if (!$autoIsolir) {
                return $this->fallbackUnpaymentComment;
            }

            $comment = $this->buildCommentString($autoIsolir, false);

            LogHelper::logInfo('MikrotikComment', [
                'customer_id' => $customer->id,
                'comment'     => $comment,
            ], 200, 'Expired comment generated successfully', $traceId, $timer->lap());

            return $comment;
        } catch (\Throwable $e) {
            LogHelper::logError('MikrotikComment', [
                'customer_id' => $customer->id,
                'error'       => $e->getMessage()
            ], 500, 'Failed expired comment', $traceId, $timer->lap());

            return $this->fallbackUnpaymentComment;
        }
    }

    /**
     * Helper terpusat untuk mengambil data AutoIsolir & mencatat Warning jika null
     */
    private function getAutoIsolir(?int $mikrotikId, array $logPayload, string $context, string $traceId, ExecutionTimer $timer): ?AutoIsolir
    {
        if (!$mikrotikId) {
            LogHelper::logWarning(
                'MikrotikComment',
                array_merge($logPayload, ['mikrotik_id' => null]),
                404,
                "AutoIsolir configuration skipped: mikrotik_id is empty for {$context} comment",
                $traceId,
                $timer->lap()
            );

            return null;
        }

        $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();

        if (!$autoIsolir) {
            LogHelper::logWarning(
                'MikrotikComment',
                array_merge($logPayload, ['mikrotik_id' => $mikrotikId]),
                404,
                "AutoIsolir configuration not found for {$context} comment",
                $traceId,
                $timer->lap()
            );
        }

        return $autoIsolir;
    }

    /**
     * Helper terpusat untuk menyusun string format komentar berdasarkan flag $isPayment
     */
    private function buildCommentString(AutoIsolir $autoIsolir, bool $isPayment = false): string
    {
        if ($isPayment) {
            return $autoIsolir->comment_payment ?? $this->fallbackPaymentComment;
        }

        return $autoIsolir->comment_unpayment ?? $this->fallbackUnpaymentComment;
    }
}
