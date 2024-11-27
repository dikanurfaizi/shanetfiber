<?php

namespace App\Http\Controllers;

use App\Models\WhatsappGateway\WhatsappGateway;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Validator;
use App\Models\WhatsappGateway\WhatsappNumber;
use App\Services\WhatsappMessageService;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendPaymentReminderJob;

class WhatsappGatewayController extends Controller
{
    private WhatsappMessageService $messageService;
    public function __construct()
    {
        $this->messageService = app(WhatsappMessageService::class);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $gateway = WhatsappGateway::where('slug', 'gateway')->first();

        //  return dd($gateway->whatsapp_number->number);
        return view('whatsapp_gateways.gateways.index', compact('gateway'));
    }

    public function edit_gateway(WhatsappGateway $whatsappgateway)
    {

        return view('whatsapp_gateways.gateways.edit_gateway', [
            'data' => $whatsappgateway
        ]);
    }

    public function edit_number(WhatsappGateway $whatsappgateway)
    {
        // $numbers =  WhatsappNumber::where('disabled', false)->get()->pluck('namenumber', 'id');
        $numbers =  WhatsappNumber::where('disabled', false)->get();
        return view('whatsapp_gateways.gateways.edit_number', [
            'data' => $whatsappgateway,
            'numbers' => $numbers
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update_number(Request $request, WhatsappGateway $whatsappgateway)
    {
        try {
            // Periksa jika gateway dinonaktifkan
            if ($request->gateway == 'false') {

                // Validasi input ketika gateway dinonaktifkan
                $this->validate(
                    $request,
                    [
                        'country_code' => 'required|numeric',
                        'remaining_day' => 'numeric|min:1|max:10',
                        'default_number' => 'required',
                    ]
                );

                $gateway_status = false;
            } else {
                // Jika gateway aktif
                $gateway_status = true;
            }

            // Update data di model WhatsappGateway
            $whatsappgateway->disabled = $gateway_status;
            $whatsappgateway->country_code = strip_tags($request->input('country_code'));
            $whatsappgateway->remaining_day = $request->input('remaining_day');
            $whatsappgateway->schedule_time = $request->input('schedule_time');
            $whatsappgateway->whatsapp_number_id = $request->input('default_number');
            $whatsappgateway->description = $request->input('description');

            // Simpan perubahan ke database
            $whatsappgateway->save();

            // Redirect dengan pesan sukses
            return redirect()->route('whatsappgateway.index')
                ->with('success', trans('whatsapp.gateway.notify.update-success'));
        } catch (\Exception $e) {
            // Tangani error jika terjadi
            Log::error('Failed to update WhatsappGateway number: ' . $e->getMessage(), [
                'request' => $request->all(),
                'whatsapp_gateway_id' => $whatsappgateway->id,
            ]);

            // Redirect dengan pesan error
            return redirect()->route('whatsappgateway.index')
                ->with('error', trans('whatsapp.gateway.notify.update-error'));
        }
    }

    public function update_gateway(Request $request, WhatsappGateway $whatsappgateway)
    {
        try {
            // Validasi input dari request
            $this->validate(
                $request,
                [
                    'url_callback' => 'required|url',
                    'api_key' => 'nullable',
                    'cert_file' => 'nullable',
                ]
            );

            // Update data di model WhatsappGateway
            $whatsappgateway->url_callback = $request->input('url_callback');
            $whatsappgateway->api_key = $request->input('api_key');
            $whatsappgateway->cert_file = $request->input('cert_file');

            // Simpan perubahan ke database
            $whatsappgateway->save();

            // Kembalikan response sukses
            return redirect()->route('whatsappgateway.index')
                ->with('success', trans('whatsapp.gateway.notify.update-success'));
        } catch (\Exception $e) {
            // Tangani error, misalnya log kesalahan dan beri feedback ke pengguna
            Log::error('Failed to update WhatsappGateway: ' . $e->getMessage());

            return redirect()->route('whatsappgateway.index')
                ->with('error', trans('whatsapp.gateway.notify.update-error'));
        }
    }

    public function test_gateway(Request $request, WhatsappGateway $whatsappgateway)
    {
        try {
            //Log::info('Testing gateway for WhatsappGateway ID: ' . $whatsappgateway->id);

            if (!$whatsappgateway->disabled) {
                Log::info('Gateway is enabled. Proceeding with validation.');
                Log::info('Value Whatsapp gateway: ' . $whatsappgateway);

                // Validasi jika url_callbak tidak boleh null
                if (empty(trim($whatsappgateway->url_callback))) {
                    Log::error('Validation failed: url_callback is required.');
                    return redirect()->route('whatsappgateway.index')
                        ->withErrors(['url_callback' => trans('whatsapp.gateway.notify.error-callback')]);
                }

                $this->validate(
                    $request,
                    [
                        'test_number' => 'required|numeric',
                    ]
                );
                Log::info('Validation passed.');

                $receive_number = $request->input('test_number');
                //Log::info('Input number received: ' . $receive_number);

                if (substr($receive_number, 0, 1) == '0') {
                    $receive_number = preg_replace('/^0?/', $whatsappgateway->country_code, $receive_number);
                    //Log::info('Formatted number: ' . $receive_number);
                }

                $sender_number = $whatsappgateway->whatsapp_number->number ?? 'N/A';
                //Log::info('Using sender number: ' . $sender_number);

                $this->messageService->test_gateway($receive_number, $sender_number);
                //Log::info('Message sent successfully to: ' . $receive_number);

                return redirect()->route('whatsappgateway.index')
                    ->with('success', trans('whatsapp.gateway.notify.test-success'));
            } else {
                Log::warning('Gateway is disabled for WhatsappGateway ID: ' . $whatsappgateway->id);
                return redirect()->route('whatsappgateway.index')
                    ->with('error', trans('whatsapp.gateway.notify.test-error'));
            }
        } catch (\Exception $e) {
            Log::error('Error occurred during test_gateway: ' . $e->getMessage());
            Log::debug('Exception trace: ' . $e->getTraceAsString());

            return redirect()->route('whatsappgateway.index')
                ->with('error', 'An error occurred while testing the gateway. Please check logs for details.');
        }
    }

    public function dispatchJob()
    {
        // Dispatch job
        dispatch(new SendPaymentReminderJob(app(WhatsappMessageService::class)));

        // Redirect dengan SweetAlert message
        return redirect()->route('whatsappgateway.index')
            ->with('success', trans('whatsapp.gateway.notify.job-dispatched'));
    }
}
