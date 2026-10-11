<?php

namespace App\Services\Event;

use App\Events\SingleNotification\CustomerActivated;
use App\Events\SingleNotification\DirectMessageRequested;
use App\Events\SingleNotification\PayLaterRequest;
use App\Events\SingleNotification\PaymentSucceeded;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Customers\CustomerSubscription;
use App\Models\Membership\CustomerMembership;
use App\Models\Membership\Membership;
use App\Models\Messaging\MessageEvent;
use App\Models\Receipt;
use App\Services\Notification\WhatsApp\Broadcast\BroadcastManagerService;
use App\Services\Notification\WhatsApp\Broadcast\PaymentChannelUpdateNotificationService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class ResendEventService
{
    public function __construct(
        protected BroadcastManagerService $broadcastManagerService,
    ) {}

    public function handle(MessageEvent $event, string $traceId): void
    {
        $timer = new ExecutionTimer();

        // Guard clause awal: Hanya melayani channel go_whatsapp
        if ($event->channel !== 'go_whatsapp') {
            throw new InvalidArgumentException("Unsupported channel: {$event->channel}");
        }

        // 👉 LANGKAH 1: Set Status awal ke 'pending' di dalam transaksi singkat
        DB::transaction(function () use ($event, $traceId, $timer) {
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
                $traceId,
                $timer->lap()
            );

            $event->update([
                'status'        => 'pending',
                'stage'         => 'RETRY',
                'retry_count'   => $event->retry_count + 1,
                'last_error_at' => null,
            ]);

            LogHelper::logInfo(
                null,
                [
                    'original_event_id' => $event->id,
                    'message_type'      => $event->message_type,
                    'retry_count'       => $event->retry_count,
                    'customer_id'       => $event->customer_id,
                ],
                200,
                '[RESEND] Update Status Event',
                $traceId,
                $timer->lap()
            );
        });

        $newEvent = $event->refresh();

        // 👉 LANGKAH 2: Jalankan flow pengiriman di luar DB::transaction utama
        try {
            LogHelper::logInfo(
                null,
                [
                    'new_event_id'  => $newEvent->id,
                    'retry_count'   => $newEvent->retry_count,
                ],
                200,
                '[RESEND] Retry State Initiated, Routing Message...',
                $traceId
            );

            /*
            |--------------------------------------------------------------------------
            | PAYMENT FLOW
            |--------------------------------------------------------------------------
            */
            if (in_array($newEvent->message_type, ['payment', 'informasi_bayar'])) {
                LogHelper::logInfo(null, [], 200, '[RESEND] Routing to PAYMENT flow', $traceId);

                $receipt = DB::transaction(function () use ($newEvent) {
                    return Receipt::where('customer_id', $newEvent->customer_id)
                        ->latest()
                        ->lockForUpdate()
                        ->first();
                });

                if (!$receipt) {
                    LogHelper::logError(null, ['customer_id' => $newEvent->customer_id], 500, '[RESEND] Receipt not found', $traceId);
                    throw new RuntimeException("Receipt not found for customer {$newEvent->customer_id}");
                }

                LogHelper::logInfo(null, ['receipt_id' => $receipt->id], 200, '[RESEND] Receipt Found', $traceId);

                event(new PaymentSucceeded(
                    $receipt->id,
                    $newEvent->id,
                    $traceId
                ));

                LogHelper::logInfo(null, [], 200, '[RESEND] PAYMENT resend dispatched', $traceId);
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | ACTIVATION FLOW
            |--------------------------------------------------------------------------
            */
            if (in_array($newEvent->message_type, ['activation_customer', 'aktivasi_layanan'])) {
                LogHelper::logInfo(null, [], 200, '[RESEND] Routing to ACTIVATION flow', $traceId);

                $customerSubscription = CustomerSubscription::with(['customer', 'package'])
                    ->where('customer_id', $newEvent->customer_id)
                    ->where('status', 'active')
                    ->firstOrFail();

                LogHelper::logInfo(null, ['customer_subscription_id' => $customerSubscription->id], 200, '[RESEND] Active Subscription Found', $traceId);

                $billing = Billing::with(['billingSubscription.billingPeriode'])
                    ->whereHas(
                        'billingSubscription',
                        fn($q) => $q->where('customer_subscription_id', $customerSubscription->id)
                    )
                    ->latest()
                    ->firstOrFail();

                LogHelper::logInfo(null, ['billing_id' => $billing->id], 200, '[RESEND] Billing Found', $traceId);

                $receipt = Receipt::with('billingPayment')
                    ->whereHas(
                        'billingPayment',
                        fn($q) => $q->where('billing_id', $billing->id)
                    )
                    ->latest()
                    ->firstOrFail();

                LogHelper::logInfo(null, ['receipt_id' => $receipt->id], 200, '[RESEND] Receipt Found for Activation', $traceId);

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

                event(new CustomerActivated(
                    $customerMembershipId,
                    $customerSubscription,
                    $billing,
                    $receipt,
                    $newEvent->id,
                    $traceId
                ));

                LogHelper::logInfo(null, [], 200, '[RESEND] ACTIVATION resend dispatched', $traceId);
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | PAYLATER FLOW
            |--------------------------------------------------------------------------
            */
            if (in_array($newEvent->message_type, ['paylater', 'informasi_bayar_nanti'])) {
                LogHelper::logInfo(null, [], 200, '[RESEND] Routing to PAYLATER flow', $traceId);

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

                LogHelper::logInfo(null, ['billing_id' => $billing->id, 'event_id' => $newEvent->id], 200, '[RESEND] PAYLATER billing resolved', $traceId);

                event(new PayLaterRequest($billing->id, $newEvent->id, $traceId));

                LogHelper::logInfo(null, [], 200, '[RESEND] PAYLATER resend dispatched', $traceId);
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | PAYMENT CHANNEL UPDATE FLOW
            |--------------------------------------------------------------------------
            */
            if (in_array($newEvent->message_type, ['update_rekening_juni_2026', 'payment_channel_update'])) {
                LogHelper::logInfo(null, ['event_id' => $newEvent->id], 200, '[RESEND] Routing to PAYMENT CHANNEL UPDATE single flow', $traceId, $timer->lap());

                $this->broadcastManagerService->sendSingleEvent($newEvent, $traceId);

                LogHelper::logInfo(null, [], 200, '[RESEND] PAYMENT CHANNEL UPDATE single resend dispatched', $traceId, $timer->lap());
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | UNKNOWN KEYWORD FLOW (EVENT DRIVEN DISPATCH)
            |--------------------------------------------------------------------------
            */
            if (in_array($newEvent->message_type, ['unknown_keyword', 'info_tagihan', 'info_paket', 'menu', 'stop', 'hubungi_admin', 'gangguan'])) {
                LogHelper::logInfo(null, ['message_type' => $newEvent->message_type], 200, '[RESEND] Routing to UNKNOWN KEYWORD event flow', $traceId);

                event(new DirectMessageRequested($newEvent->id, $traceId));

                LogHelper::logInfo(null, [], 200, '[RESEND] Unknown keyword message event dispatched successfully', $traceId, $timer->lap());
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | FALLBACK: UNKNOWN CUSTOMER / CUSTOM FLOW VIA PAYLOAD (EVENT DRIVEN DISPATCH)
            |--------------------------------------------------------------------------
            */
            if (empty($newEvent->customer_id)) {
                LogHelper::logInfo(null, ['message_type' => $newEvent->message_type, 'event_id' => $newEvent->id], 200, '[RESEND] No Customer ID - Fallback dispatching direct message event from payload', $traceId, $timer->lap());

                event(new DirectMessageRequested($newEvent->id, $traceId));

                LogHelper::logInfo(null, [], 200, '[RESEND] Fallback direct message event dispatched successfully', $traceId, $timer->lap());
                return;
            }

            throw new InvalidArgumentException("Unsupported message type: {$newEvent->message_type} or invalid customer configuration.");
        } catch (Throwable $e) {
            // 👉 LANGKAH 3: Tangkap exception, amankan state DB ke status 'failed'
            LogHelper::logError(
                $e,
                ['event_id' => $newEvent->id, 'error_message' => $e->getMessage()],
                500,
                '[RESEND] Handling Exception - Updating state to FAILED',
                $traceId,
                $timer->lap()
            );

            $currentMeta = is_array($newEvent->meta) ? $newEvent->meta : [];

            $newEvent->update([
                'status'        => 'failed',
                'stage'         => 'RETRY_FAILED',
                'last_error_at' => now(),
                'meta'          => array_merge($currentMeta, [
                    'resend_error' => $e->getMessage()
                ])
            ]);

            throw $e;
        }
    }
}
