<?php

namespace App\Services\Event;

use App\Helpers\LogHelper;
use App\Models\Messaging\MessageEvent;
use App\Models\Receipt;
use App\Models\Customers\CustomerSubscription;
use App\Models\Billings\Billing;
use App\Models\Membership\CustomerMembership;
use App\Models\Membership\Membership;
use App\Services\Notification\CustomerActivationNotificationService;
use App\Services\Notification\PaymentNotificationService;
use App\Services\Notification\PaymentPayLaterNotificationService;
use Illuminate\Support\Facades\DB;

class ResendEventService
{
    public function __construct(
        protected PaymentNotificationService $paymentNotification,
        protected CustomerActivationNotificationService $activationService,
        protected PaymentPayLaterNotificationService $paylaterService,
    ) {}

    public function handle(MessageEvent $event, string $traceId): void
    {
        DB::transaction(function () use ($event, $traceId) {

            LogHelper::logInfo(
                null,
                [
                    'original_event_id' => $event->id,
                    'message_type'      => $event->message_type,
                    'retry_count'       => $event->retry_count,
                    'customer_id'       => $event->customer_id,
                ],
                200,
                '[RESEND] Start Resend Event',
                $traceId
            );

            // =========================
            // CREATE RETRY EVENT
            // =========================
            $event->update([
                'status'      => 'pending',
                'stage'       => 'RETRY',
                'retry_count' => $event->retry_count + 1,
                'last_error_at' => null,
            ]);

            $newEvent = $event->refresh();

            LogHelper::logInfo(
                null,
                [
                    'new_event_id'  => $newEvent->id,
                    'retry_of_id'   => $newEvent->retry_of_id,
                    'retry_count'   => $newEvent->retry_count,
                ],
                200,
                '[RESEND] Retry Event Created',
                $traceId
            );

            /*
        |--------------------------------------------------------------------------
        | PAYMENT
        |--------------------------------------------------------------------------
        */
            if (in_array($newEvent->message_type, ['payment', 'informasi_bayar'])) {

                LogHelper::logInfo(
                    null,
                    [],
                    200,
                    '[RESEND] Routing to PAYMENT flow',
                    $traceId
                );

                $receipt = Receipt::where('customer_id', $newEvent->customer_id)
                    ->latest()
                    ->lockForUpdate()
                    ->first();

                if (!$receipt) {
                    LogHelper::logError(
                        null,
                        ['customer_id' => $newEvent->customer_id],
                        500,
                        '[RESEND] Receipt not found',
                        $traceId
                    );

                    throw new \RuntimeException("Receipt not found for customer {$newEvent->customer_id}");
                }

                LogHelper::logInfo(
                    null,
                    [
                        'receipt_id' => $receipt->id,
                    ],
                    200,
                    '[RESEND] Receipt Found',
                    $traceId
                );

                $this->paymentNotification
                    ->sendWhatsAppPaymentNotificationByReceiptId(
                        $receipt->id,
                        $traceId,
                        $newEvent->id
                    );

                LogHelper::logInfo(
                    null,
                    [],
                    200,
                    '[RESEND] PAYMENT resend dispatched',
                    $traceId
                );

                return;
            }

            /*
        |--------------------------------------------------------------------------
        | ACTIVATION
        |--------------------------------------------------------------------------
        */
            if (in_array($newEvent->message_type, ['activation_customer', 'aktivasi_layanan'])) {

                LogHelper::logInfo(
                    null,
                    [],
                    200,
                    '[RESEND] Routing to ACTIVATION flow',
                    $traceId
                );

                $customerSubscription = CustomerSubscription::with(['customer', 'package'])
                    ->where('customer_id', $newEvent->customer_id)
                    ->where('status', 'active')
                    ->firstOrFail();

                LogHelper::logInfo(
                    null,
                    [
                        'customer_subscription_id' => $customerSubscription->id,
                    ],
                    200,
                    '[RESEND] Active Subscription Found',
                    $traceId
                );

                $billing = Billing::with(['billingSubscription.billingPeriode'])
                    ->whereHas(
                        'billingSubscription',
                        fn($q) => $q->where('customer_subscription_id', $customerSubscription->id)
                    )
                    ->latest()
                    ->firstOrFail();

                LogHelper::logInfo(
                    null,
                    ['billing_id' => $billing->id],
                    200,
                    '[RESEND] Billing Found',
                    $traceId
                );

                $receipt = Receipt::with('billingPayment')
                    ->whereHas(
                        'billingPayment',
                        fn($q) => $q->where('billing_id', $billing->id)
                    )
                    ->latest()
                    ->firstOrFail();

                LogHelper::logInfo(
                    null,
                    ['receipt_id' => $receipt->id],
                    200,
                    '[RESEND] Receipt Found for Activation',
                    $traceId
                );

                $customerMembershipId = null;

                if ($receipt->member_type && $receipt->member_type !== 'NON_MEMBER') {

                    $membership = Membership::where('name', $receipt->member_type)->first();

                    if ($membership) {
                        $customerMembership = CustomerMembership::where('membership_id', $membership->id)
                            ->where('customer_id', $customerSubscription->customer_id)
                            ->first();

                        $customerMembershipId = $customerMembership?->id;

                        LogHelper::logInfo(
                            null,
                            [
                                'membership_id' => $membership->id,
                                'customer_membership_id' => $customerMembershipId,
                            ],
                            200,
                            '[RESEND] Membership Resolved',
                            $traceId
                        );
                    }
                }

                $this->activationService->send(
                    $customerMembershipId,
                    $customerSubscription,
                    $billing,
                    $receipt,
                    $newEvent->id,
                    $traceId
                );

                LogHelper::logInfo(
                    null,
                    [],
                    200,
                    '[RESEND] ACTIVATION resend dispatched',
                    $traceId
                );

                return;
            }

            /*
        |--------------------------------------------------------------------------
        | PAYLATER
        |--------------------------------------------------------------------------
        */
            if (in_array($newEvent->message_type, ['paylater', 'informasi_bayar_nanti'])) {

                LogHelper::logInfo(
                    null,
                    [],
                    200,
                    '[RESEND] Routing to PAYLATER flow',
                    $traceId
                );

                $customerSubscription = CustomerSubscription::with(['customer', 'package'])
                    ->where('customer_id', $newEvent->customer_id)
                    ->where('status', 'active')
                    ->firstOrFail();

                $billing = Billing::with(['billingSubscription.billingPeriode'])
                    ->whereHas(
                        'billingSubscription',
                        fn($q) => $q->where('customer_subscription_id', $customerSubscription->id)
                    )
                    ->latest()
                    ->firstOrFail();

                LogHelper::logInfo(
                    null,
                    ['billing_id' => $billing->id, 'event_id' => $newEvent->id],
                    200,
                    '[RESEND] PAYLATER billing resolved',
                    $traceId
                );

                $this->paylaterService->send(
                    $billing->id,
                    $newEvent->id,
                    $traceId
                );

                LogHelper::logInfo(
                    null,
                    [],
                    200,
                    '[RESEND] PAYLATER resend dispatched',
                    $traceId
                );

                return;
            }

            LogHelper::logError(
                null,
                ['message_type' => $newEvent->message_type],
                500,
                '[RESEND] Unsupported message type',
                $traceId
            );

            throw new \InvalidArgumentException(
                "Unsupported message type: {$newEvent->message_type}"
            );
        });
    }
}
