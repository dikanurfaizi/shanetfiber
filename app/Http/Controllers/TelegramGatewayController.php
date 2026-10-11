<?php

namespace App\Http\Controllers;

use App\Events\SingleNotification\TelegramBotLinkRequest;
use App\Models\Telegram\TelegramGateway;
use App\Models\WhatsApp\WhatsAppGateway;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class TelegramGatewayController extends Controller
{
    public function __construct() {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $gateway = TelegramGateway::where('slug', 'gateway')->first();

        //  return dd($gateway->whatsapp_number->number);
        return view('telegram_gateways.gateways.index', compact('gateway'));
    }

    public function edit_description(TelegramGateway $telegramGateway)
    {
        if ($telegramGateway->basic_auth_password) {
            $telegramGateway->basic_auth_password = Crypt::decryptString($telegramGateway->basic_auth_password);
        }
        return view('telegram_gateways.gateways.edit_description', [
            'data' => $telegramGateway
        ]);
    }

    public function edit_gateway(TelegramGateway $telegramGateway)
    {
        if ($telegramGateway->basic_auth_password) {
            $telegramGateway->basic_auth_password = Crypt::decryptString($telegramGateway->basic_auth_password);
        }
        return view('telegram_gateways.gateways.edit_gateway', [
            'data' => $telegramGateway
        ]);
    }


    /**
     * Update the specified resource in storage.
     */

    public function update_gateway(Request $request, TelegramGateway $telegramGateway)
    {
        try {
            $this->validate(
                $request,
                [
                    'url_callback' => 'nullable',
                    'token' => 'nullable',
                    'cert_file' => 'nullable',
                ]
            );

            // Update data di model WhatsappGateway
            $telegramGateway->url_callback = $request->input('url_callback');
            $telegramGateway->token = $request->input('token');
            $telegramGateway->cert_file = $request->input('cert_file');
            $telegramGateway->auth_disabled = $request->input('auth_disabled');
            $telegramGateway->basic_auth_username = $request->input('basic_auth_username') ? $request->input('basic_auth_username') : null;
            if ($request->input('basic_auth_password')) {
                $telegramGateway->basic_auth_password = Crypt::encryptString($request->input('basic_auth_password'));
            } else {
                $telegramGateway->basic_auth_password = null;
            }

            // dd($request, $telegramGateway);

            // Simpan perubahan ke database
            $telegramGateway->update();

            // Kembalikan response sukses
            return redirect()->route('telegramgateway.index')
                ->with('success', trans('telegram.gateway.notify.update-success'));
        } catch (\Exception $e) {
            // Tangani error, misalnya log kesalahan dan beri feedback ke pengguna
            Log::error('Failed to update TelegramGateway: ' . $e->getMessage());

            return redirect()->route('telegramgateway.index')
                ->with('error', trans('telegram.gateway.notify.update-error'));
        }
    }

    public function update_description(Request $request, TelegramGateway $telegramGateway)
    {
        try {
            $this->validate($request, [
                'gateway' => 'nullable',
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            $data = $request->only(['gateway', 'name', 'description']);

            // Jika 'gateway' tidak diisi, set default ke 0
            $data['gateway'] = $request->filled('gateway') ? $request->input('gateway') : 0;

            // Update hanya 3 field
            $telegramGateway->update($data);

            return redirect()->route('telegramgateway.index')
                ->with('success', trans('telegram.gateway.notify.update-success'));
        } catch (\Exception $e) {
            Log::error('Failed to update TelegramGateway: ' . $e->getMessage());

            return redirect()->route('telegramgateway.index')
                ->with('error', trans('telegram.gateway.notify.update-error'));
        }
    }

    public function test_gateway(Request $request)
    {
        $traceId = (string) Str::uuid();

        try {
            // Ambil gateway
            $telegramGateway = TelegramGateway::first();
            $whatsAppGateway = WhatsAppGateway::first();

            // Validasi gateway aktif
            if ($telegramGateway?->disabled || $whatsAppGateway?->disabled) {
                LogHelper::logWarning(
                    null,
                    ['telegram_gateway' => $telegramGateway, 'whatsapp_gateway' => $whatsAppGateway],
                    503,
                    "One or more gateways are disabled"
                );

                return redirect()->route('telegramgateway.index')
                    ->with('error', trans('telegram.gateway.notify.test-error'));
            }

            // Validasi input nomor
            $request->validate(['test_number' => 'required|numeric']);
            $receiveNumber = $request->input('test_number');

            // Format nomor tujuan
            if (str_starts_with($receiveNumber, '0')) {
                $receiveNumber = preg_replace(
                    '/^0/',
                    $whatsAppGateway->country_code ?? '62',
                    $receiveNumber
                );
            }

            event(new TelegramBotLinkRequest(
                receiverNumber: $receiveNumber,
                telegramGateway: $telegramGateway,
                eventId: null,
                traceId: $traceId
            ));

            LogHelper::logInfo(
                null,
                [
                    'gateway_id' => $telegramGateway->id,
                    'receiver' => $receiveNumber
                ],
                200,
                "Test message successfully sent via WhatsApp gateway"
            );

            return redirect()->route('telegramgateway.index')
                ->with('success', trans('whatsapp.gateway.notify.test-success'));
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['message_error' => $e->getMessage()],
                500,
                "Error to send WhatsApp test message"
            );

            LogHelper::logWarning(
                null,
                ['trace_as_string' => $e->getTraceAsString()],
                500,
                "Exception trace"
            );

            return redirect()->route('telegramgateway.index')
                ->with('error', 'Terjadi kesalahan saat mengirim pesan uji coba. Silakan periksa log.');
        }
    }
}
