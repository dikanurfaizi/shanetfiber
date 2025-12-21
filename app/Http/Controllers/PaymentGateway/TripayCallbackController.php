<?php

namespace App\Http\Controllers\PaymentGateway;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Traits\TripayHelper;
use App\Helpers\LogHelper;
use Illuminate\Support\Carbon;
use App\Services\Tripays\TripayCallbackOrchestrator;
use Illuminate\Support\Str;

class TripayCallbackController extends Controller
{
    use TripayHelper;

    private TripayCallbackOrchestrator $orchestrator;

    public function __construct(TripayCallbackOrchestrator $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    public function handle(Request $request)
    {
        $traceId = (string) Str::uuid();
        $startTime = microtime(true);
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
                ->where('payment_status', OrderStatus::UNPAID)
                ->first();

            if (!$order) {
                return Response::json([
                    'success' => false,
                    'message' => 'No order found or already processed: ' . $invoiceId,
                ]);
            }

            switch ($status) {
                case 'UNPAID':
                    $this->orchestrator->handleUnpaid(order: $order, traceId: $traceId);
                    break;

                case 'PAID':
                    $this->orchestrator->handlePaid(
                        order: $order,
                        paidAt: $paid_at,
                        traceId: $traceId
                    );
                    break;

                case 'EXPIRED':
                    $this->orchestrator->handleExpired($order, OrderStatus::EXPIRED->value, $traceId);
                    break;

                case 'FAILED':
                    $this->orchestrator->handleCanceled($order, OrderStatus::CANCELED->value, $traceId);
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
