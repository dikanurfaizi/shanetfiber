<?php

namespace App\Services\Customers;

use App\Events\SingleNotification\ActivateCustomerEvent;
use App\Events\SingleNotification\CustomerActivated;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPayment;
use App\Models\Billings\BillingPeriode;
use App\Models\Billings\BillingSubscriptionPeriode;
use App\Models\Customers\Customer;
use Throwable;

class CustomerResendActivationService
{
    /**
     * Memproses pengiriman ulang email aktivasi pelanggan.
     */
    public function resendActivation(Customer $customer, string $traceId, ?ExecutionTimer $timer = null): array
    {
        $timer ??= new ExecutionTimer();

        LogHelper::logInfo(
            null,
            [
                'customer_id'   => $customer->id,
                'customer_slug' => $customer->slug,
                'email'         => $customer->email,
            ],
            200,
            'Resend activation requested.',
            $traceId,
            $timer->lap()
        );

        try {
            // 1. Ambil Billing Periode terbaru
            $latestPeriode = BillingPeriode::orderByDesc('id')->first();
            $membership = $customer->activeMembership;
            $membershipId = $membership->id ?? null;

            $latestBilling = null;

            if ($latestPeriode) {
                // 2. Ambil subscription aktif
                $activeSubscription = $customer->activeSubscription;

                if ($activeSubscription) {
                    // 3. Cari pivot billing subscription periode
                    $billingSubscriptionPeriode = BillingSubscriptionPeriode::where('customer_subscription_id', $activeSubscription->id)
                        ->where('billing_periode_id', $latestPeriode->id)
                        ->first();

                    if ($billingSubscriptionPeriode) {
                        // 4. Cari billing terkait
                        $latestBilling = Billing::where('billing_subscription_id', $billingSubscriptionPeriode->id)
                            ->where('customer_id', $customer->id)
                            ->first();
                    }
                }
            }

            // 5. Cari receipt terakhir
            $billingPayment = BillingPayment::where('billing_id', $latestBilling->id ?? null)
                ->latest()
                ->first();

            $latestReceipt = $billingPayment ? $billingPayment->receipt : null;
            $activeSubscription = $customer->activeSubscription;

            // 6. Dispatch Event Aktivasi Ulang
            event(
                new ActivateCustomerEvent(
                    $membershipId,
                    $activeSubscription,
                    $latestBilling,
                    $latestReceipt,
                    null,
                    $traceId
                )
            );

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'billing_id'  => $latestBilling->id ?? null,
                    'receipt_id'  => $latestReceipt->id ?? null,
                ],
                200,
                'Resend activation email event dispatched successfully.',
                $traceId,
                $timer->lap()
            );

            return [
                'success' => true,
                'message' => __('customer.message.resend-activation-success', ['name' => $customer->name]),
            ];
        } catch (Throwable $e) {
            LogHelper::logError(
                $e,
                [
                    'customer_id' => $customer->id,
                    'exception'   => get_class($e),
                    'message'     => $e->getMessage(),
                ],
                500,
                'Failed to resend activation email.',
                $traceId,
                $timer->lap()
            );

            return [
                'success' => false,
                'message' => __('customer.message.resend-activation-failed'),
            ];
        }
    }
}
