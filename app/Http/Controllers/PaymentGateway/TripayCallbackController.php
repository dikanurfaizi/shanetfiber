<?php

namespace App\Http\Controllers\PaymentGateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Traits\TripayHelper;
use App\Helpers\LogHelper;
use Illuminate\Support\Carbon;
use App\Services\Tripay\CallbackPaymentService;
use Illuminate\Support\Str;

class TripayCallbackController extends Controller
{
    use TripayHelper;

    private CallbackPaymentService $callbackPaymentService;

    public function __construct(CallbackPaymentService $callbackPaymentService)
    {
        $this->callbackPaymentService = $callbackPaymentService;
    }

    public function handle(Request $request)
    {
        $traceId = (string) Str::uuid();
        $tripay = $this->getTripayData();

        if (!$tripay) {
            return Response::json([
                'success' => false,
                'message' => 'Tripay config not found',
            ], 404);
        }

        $privateKey = $tripay->tripays_is_production === 'false'
            ? $tripay->tripays_sandbox_private_key
            : $tripay->tripays_private_key;

        $callbackSignature = $request->header('X-Callback-Signature')
            ?? $_SERVER['HTTP_X_CALLBACK_SIGNATURE']
            ?? null;

        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, $privateKey);

        // return response()->json([
        //     'callbackSignature' => $callbackSignature,
        //     'signature' => $signature,
        // ]);

        if ($signature !== (string) $callbackSignature) {
            Log::warning('Tripay Signature Mismatch', [
                'expected' => $signature,
                'received' => $callbackSignature,
            ]);
            LogHelper::logWarning(null, [
                'callbackSignature' => $callbackSignature,
                'signature' => $signature,
            ], 400, "Invalid Signature");
            return Response::json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 403);
        }

        $callbackEvent = (string) ($request->header('X-Callback-Event')
            ?? $request->server('HTTP_X_CALLBACK_EVENT')
            ?? '');

        // return Response::json([
        //     'payment_status' => $callbackEvent,  // This will show the event you're checking
        //     'callback_event' => $callbackEvent,  // Just to confirm which event is received
        // ]);

        if ('payment_status' !== (string) $callbackEvent) {
            return Response::json([
                'success' => false,
                'message' => 'Unrecognized callback event, no action was taken',
            ]);
        }

        $data = json_decode($json);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid JSON sent by Tripay',
            ]);
        }

        $invoiceId = $data->merchant_ref ?? null;
        $tripayReference = $data->reference ?? null;
        $status = strtoupper((string) ($data->status ?? ''));
        $paid_at = Carbon::createFromTimestamp($data->paid_at) ?? Carbon::now();

        if (!$invoiceId || !$tripayReference || !$status) {
            return Response::json([
                'success' => false,
                'message' => 'Incomplete data received',
            ]);
        }

        if ($data->is_closed_payment === 1) {
            $order = Order::where('merchant_ref', $invoiceId)
                ->where('reference', $tripayReference)
                ->where('payment_status', Order::STATUS_UNPAID)
                ->first();

            if (!$order) {
                return Response::json([
                    'success' => false,
                    'message' => 'No order found or already processed: ' . $invoiceId,
                ]);
            }

            switch ($status) {
                case 'UNPAID':
                    $order->update(['payment_status' => Order::STATUS_UNPAID]);
                    $this->callbackPaymentService->processPaymentUnpaid($order, $order->billing, $traceId);
                    break;

                case 'PAID':
                    $order->update(['payment_status' => Order::STATUS_PAID]);
                    $this->callbackPaymentService->processPaymentSuccess($order, $order->billing, $paid_at, $traceId);
                    break;

                case 'EXPIRED':
                    $order->update(['payment_status' => Order::STATUS_EXPIRED]);
                    $this->callbackPaymentService->processUpdatePaymentStatus($order, Order::STATUS_EXPIRED, $traceId);
                    break;

                case 'FAILED':
                    $order->update(['payment_status' => Order::STATUS_CANCELED]);
                    $this->callbackPaymentService->processUpdatePaymentStatus($order, Order::STATUS_CANCELED, $traceId);
                    break;

                default:
                    return Response::json([
                        'success' => false,
                        'message' => 'Unrecognized payment status',
                    ]);
            }

            Log::info('Tripay Payment Callback processed', [
                'order_id' => $order->id,
                'status'   => $status,
            ]);

            return Response::json(['success' => true]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Callback is not closed payment type',
        ]);
    }
}
