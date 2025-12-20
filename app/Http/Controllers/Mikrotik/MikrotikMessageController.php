<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\WhatsApp\SendWhatsappMessageJob;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use Illuminate\Support\Str;

class MikrotikMessageController extends Controller
{
    public function send(Request $request)
    {
        $traceId = (string) Str::uuid();

        // Log permintaan awal dari MikroTik
        LogHelper::logInfo(null, [
            'headers' => $request->headers->all(),
            'body'    => $request->all(),
        ], 200, 'Request received from Mikrotik', $traceId);

        $phone   = $request->input('phone') ?? $request->input('number');
        $message = $request->input('text')  ?? $request->input('message');

        if (!$phone || !$message) {
            LogHelper::logInfo(null, [
                'missing' => [
                    'phone'   => $phone,
                    'message' => $message,
                ]
            ], 422, 'Missing phone or message', $traceId);

            return response()->json([
                'status'  => 'error',
                'message' => 'phone/number dan text/message wajib diisi'
            ], 422);
        }

        $formatter = new FormatReceiver();
        $formattedReceiver = $formatter->formatReceiver($phone, 'broadcast');

        $customer = Customer::where('contact', $formattedReceiver)->first();
        $customerId = $customer?->id ?? null;

        if (!$customerId) {
            LogHelper::logInfo(null, [
                'formatted' => $formattedReceiver,
            ], 404, "Customer Not Found", $traceId);

            return response()->json([
                'status' => 'error',
                'message' => 'Customer tidak ditemukan untuk nomor tersebut'
            ], 404);
        }

        $messageType = 'mikrotik';

        SendWhatsappMessageJob::dispatch(
            $messageType,
            $formattedReceiver,
            $message,
            $customerId,
            $traceId
        )->delay(now()->addSeconds(1));

        LogHelper::logInfo(null, [
            'to'      => $formattedReceiver,
            'message' => $message,
            'customer_id' => $customerId,
        ], 200, 'WA Job dispatched', $traceId);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pesan dijadwalkan dikirim'
        ]);
    }
}
