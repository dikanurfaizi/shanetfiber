<?php

namespace App\Http\Controllers\Telegram;

use App\Events\SingleNotification\TelegramBotLinkRequest;
use App\Helpers\FormatReceiver;
use App\Models\Telegram\TelegramGateway;
use App\Models\WhatsApp\WhatsAppGateway;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class GatewayController extends Controller
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
            $this->validate($request, [
                'url_callback'        => 'nullable|string',
                'token'               => 'nullable|string',
                'cert_file'           => 'nullable|string',
                'auth_disabled'       => 'nullable|boolean',
                'basic_auth_username' => 'nullable|string',
                'basic_auth_password' => 'nullable|string',
            ]);

            // 1. Update field umum hanya jika input dikirimkan (tidak null)
            if ($request->has('url_callback')) {
                $telegramGateway->url_callback = $request->input('url_callback');
            }

            if ($request->has('token')) {
                $telegramGateway->token = $request->input('token');
            }

            if ($request->has('cert_file')) {
                $telegramGateway->cert_file = $request->input('cert_file');
            }

            if ($request->has('auth_disabled')) {
                $telegramGateway->auth_disabled = $request->boolean('auth_disabled');
            }

            if ($request->has('basic_auth_username')) {
                $telegramGateway->basic_auth_username = $request->input('basic_auth_username');
            }

            // 2. Password hanya dienkripsi & diperbarui jika diisi (filled)
            // Jika form password dikosongkan saat update, gunakan nilai password lama
            if ($request->filled('basic_auth_password')) {
                $telegramGateway->basic_auth_password = Crypt::encryptString($request->input('basic_auth_password'));
            }

            // 3. Simpan perubahan (Laravel otomatis mendeteksi isDirty, jika tidak ada nilai yang berubah maka query UPDATE di-skip)
            $telegramGateway->save();

            return redirect()->route('telegramgateway.index')
                ->with('success', trans('telegram.gateway.notify.update-success'));
        } catch (\Exception $e) {
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
            // 1. Validasi input nomor penerima (Mandatory & Numeric)
            $validator = Validator::make($request->all(), [
                'test_number' => 'required|numeric',
            ], [
                'test_number.required' => 'Nomor WhatsApp penerima wajib diisi (mandatory).',
                'test_number.numeric'  => 'Nomor WhatsApp penerima harus berupa angka.',
            ]);

            if ($validator->fails()) {
                return redirect()->route('telegramgateway.index')
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Nomor WhatsApp penerima wajib diisi (mandatory).');
            }

            $receiveNumber = trim($request->input('test_number'));

            // Guard tambahan jika value kosong setelah trim
            if (blank($receiveNumber)) {
                return redirect()->route('telegramgateway.index')
                    ->withInput()
                    ->with('error', 'Nomor WhatsApp penerima wajib diisi (mandatory).');
            }

            // Ambil data Gateway
            $telegramGateway = TelegramGateway::first();
            $whatsAppGateway = WhatsAppGateway::first();

            // 2. Validasi status aktif Gateway
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

            $formatter = new FormatReceiver();
            $receiver = $formatter->formatReceiver($receiveNumber, null, false);

            // 3. Trigger Event
            event(new TelegramBotLinkRequest($receiver, $telegramGateway, null, $traceId));

            LogHelper::logInfo(
                null,
                [
                    'gateway_id' => $telegramGateway->id,
                    'receiver'   => $receiveNumber,
                ],
                200,
                "Test message event successfully triggered"
            );

            return redirect()->route('telegramgateway.index')
                ->with('success', trans('whatsapp.gateway.notify.test-success'));
        } catch (ValidationException $e) {
            return redirect()->route('telegramgateway.index')
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Nomor WhatsApp penerima wajib diisi (mandatory).');
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['message_error' => $e->getMessage()],
                500,
                "Error to trigger Telegram bot link test event"
            );

            return redirect()->route('telegramgateway.index')
                ->with('error', 'Terjadi kesalahan saat menguji pesan Telegram bot. Silakan periksa log.');
        }
    }
}
