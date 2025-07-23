<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\SendWhatsappSingleJob;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Customer;

class MikrotikMessageController extends Controller
{
    public function send(Request $request)
    {
        $phone   = $request->input('phone') ?? $request->input('number');
        $message = $request->input('text')  ?? $request->input('message');

        if (!$phone || !$message) {
            return response()->json([
                'status'  => 'error',
                'message' => 'phone/number dan text/message wajib diisi'
            ], 422);
        }

        $formatter = new FormatReceiver();
        $formattedReceiver = $formatter->formatReceiver($phone, 'broadcast');

        logHelper::logInfo(null, ['real-contact' => $phone, 'contact' => $formattedReceiver], 200, "Contact Customer");

        $customer = Customer::where('contact', $formattedReceiver)->first();
        $customerId = $customer?->id ?? null;

        if (!$customerId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer tidak ditemukan untuk nomor tersebut'
            ], 404);
        }

        $messageType = 'mikrotik';

        SendWhatsappSingleJob::dispatch(
            $messageType,
            $formattedReceiver,
            $message,
            $customerId
        )->delay(now()->addSeconds(1));

        return response()->json([
            'status'  => 'success',
            'message' => 'Pesan dijadwalkan dikirim'
        ]);
    }
}
