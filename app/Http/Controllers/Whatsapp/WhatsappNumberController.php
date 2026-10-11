<?php

namespace App\Http\Controllers\WhatsApp;

use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\WhatsApp\WhatsAppGateway;
use App\Models\WhatsApp\WhatsAppNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class WhatsAppNumberController extends Controller
{
    public function formatPhoneNumber(string $number, ?WhatsAppGateway $gateway = null): string
    {
        $countryCode = $gateway->country_code ?? '62';

        // Hapus semua karakter non-digit kecuali "+"
        $number = preg_replace('/[^\d+]/', '', $number);

        // Hapus tanda "+" di awal jika ada
        if (str_starts_with($number, '+')) {
            $number = substr($number, 1);
        }

        // Hapus strip/tanda hubung
        $number = str_replace('-', '', $number);

        // Format awalan sesuai country_code
        if (str_starts_with($number, '62')) {
            $number = preg_replace('/^62/', $countryCode, $number);
        } elseif (str_starts_with($number, '0')) {
            $number = preg_replace('/^0/', $countryCode, $number);
        } else {
            $number = $countryCode . $number;
        }

        return $number;
    }

    private function getGateway(): ?WhatsAppGateway
    {
        return WhatsAppGateway::where('slug', 'gateway')->first();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        LogHelper::logInfo(null, [
            'page' => $request->input('page', 1),
        ], 200, '[WhatsAppNumberController@index] Fetching numbers list', $traceId, $timer->lap());

        $numbers = WhatsAppNumber::orderBy('name', 'ASC')->paginate(10);

        return view('whatsapp_gateways.numbers.index', compact('numbers'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('whatsapp_gateways.numbers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        $request->validate([
            'name'      => 'required|string|max:255|unique:whatsapp_numbers,name',
            'number'    => 'required|numeric|unique:whatsapp_numbers,number',
            'device_id' => 'nullable|string|max:255|unique:whatsapp_numbers,device_id',
        ], [
            'name.unique' => "Name {$request->name} already exists.",
        ]);

        DB::beginTransaction();

        try {
            $gateway = $this->getGateway();
            $formattedNumber = $this->formatPhoneNumber($request->input('number'), $gateway);
            $headerName = 'Shanet-Signature';

            $newNumber = WhatsAppNumber::create([
                'slug'                  => Str::slug(Str::random(5)),
                'name'                  => $request->input('name'),
                'number'                => $formattedNumber,
                'webhook_client_header' => $headerName,
                'webhook_client_secret' => Str::random(15),
                'description'          => $request->input('description'),
                'device_id'             => $request->input('device_id'),
            ]);

            DB::commit();

            LogHelper::logInfo(null, [
                'number_id' => $newNumber->id,
                'name'      => $newNumber->name,
                'number'    => $newNumber->number,
                'device_id' => $newNumber->device_id,
            ], 200, '[WhatsAppNumberController@store] Number created successfully', $traceId, $timer->lap());

            return redirect()->route('whatsappnumber.index')
                ->with('success', trans('whatsapp.number.notify.success-save-number', ['number' => $newNumber->number]));
        } catch (Throwable $e) {
            DB::rollBack();

            $statusCode = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() <= 599)
                ? (int) $e->getCode()
                : 500;

            LogHelper::logError(null, [
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
                'input'           => $request->except(['_token']),
            ], $statusCode, '[WhatsAppNumberController@store] Failed to save number', $traceId, $timer->lap());

            return back()->withInput()->with('error', 'Failed to save WhatsApp number: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WhatsAppNumber $whatsappnumber)
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        LogHelper::logInfo(null, [
            'number_id' => $whatsappnumber->id,
            'name'      => $whatsappnumber->name,
        ], 200, '[WhatsAppNumberController@show] Viewing number details', $traceId, $timer->lap());

        $computedSignature = hash_hmac('sha512', $whatsappnumber->number, (string) $whatsappnumber->webhook_client_secret);

        return view('whatsapp_gateways.numbers.show', [
            'data'   => $whatsappnumber,
            'secret' => $computedSignature,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WhatsAppNumber $whatsappnumber)
    {
        return view('whatsapp_gateways.numbers.edit', [
            'data' => $whatsappnumber,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WhatsAppNumber $whatsappnumber)
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        $request->validate([
            'name'      => ['required', 'string', 'max:255', Rule::unique('whatsapp_numbers', 'name')->ignore($whatsappnumber->id)],
            'number'    => ['required', 'string', Rule::unique('whatsapp_numbers', 'number')->ignore($whatsappnumber->id)],
            'device_id' => ['nullable', 'string', 'max:255', Rule::unique('whatsapp_numbers', 'device_id')->ignore($whatsappnumber->id)],
        ], [
            'name.unique' => "Name {$request->name} already exists.",
        ]);

        DB::beginTransaction();

        try {
            $gateway = $this->getGateway();
            $formattedNumber = $this->formatPhoneNumber($request->input('number'), $gateway);

            $whatsappnumber->update([
                'name'        => $request->input('name'),
                'number'      => $formattedNumber,
                'device_id'   => $request->input('device_id'),
                'description' => $request->input('description'),
            ]);

            DB::commit();

            LogHelper::logInfo(null, [
                'number_id' => $whatsappnumber->id,
                'name'      => $whatsappnumber->name,
                'number'    => $whatsappnumber->number,
            ], 200, '[WhatsAppNumberController@update] Number updated successfully', $traceId, $timer->lap());

            return redirect()->route('whatsappnumber.index')
                ->with('success', trans('whatsapp.number.notify.success-update-number', ['name' => $whatsappnumber->name]));
        } catch (Throwable $e) {
            DB::rollBack();

            $statusCode = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() <= 599)
                ? (int) $e->getCode()
                : 500;

            LogHelper::logError(null, [
                'number_id'       => $whatsappnumber->id,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, '[WhatsAppNumberController@update] Failed to update number', $traceId, $timer->lap());

            return back()->withInput()->with('error', 'Failed to update WhatsApp number: ' . $e->getMessage());
        }
    }

    /**
     * Disable/Enable number status.
     */
    public function disable_number(Request $request, WhatsAppNumber $whatsappnumber)
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        DB::beginTransaction();

        try {
            $whatsappnumber->is_enabled = !$whatsappnumber->is_enabled;
            $whatsappnumber->save();

            DB::commit();

            LogHelper::logInfo(null, [
                'number_id'  => $whatsappnumber->id,
                'is_enabled' => $whatsappnumber->is_enabled,
            ], 200, '[WhatsAppNumberController@disable_number] Status toggled successfully', $traceId, $timer->lap());

            return redirect()->route('whatsappnumber.index')
                ->with('success', trans('whatsapp.number.notify.success-update-number', ['name' => $whatsappnumber->name]));
        } catch (Throwable $e) {
            DB::rollBack();

            LogHelper::logError(null, [
                'number_id'     => $whatsappnumber->id,
                'error_message' => $e->getMessage(),
            ], 500, '[WhatsAppNumberController@disable_number] Failed to toggle number status', $traceId, $timer->lap());

            return back()->with('error', 'Failed to change number status.');
        }
    }

    /**
     * Show delete confirmation form.
     */
    public function delete(WhatsAppNumber $whatsappnumber)
    {
        return view('whatsapp_gateways.numbers.delete', [
            'whatsAppNumber' => $whatsappnumber,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WhatsAppNumber $whatsappnumber)
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        DB::beginTransaction();

        try {
            // Pengecekan relasi ke gateways
            if ($whatsappnumber->account()->exists()) {
                LogHelper::logWarning(null, [
                    'number_id' => $whatsappnumber->id,
                    'number'    => $whatsappnumber->number,
                ], 400, '[WhatsAppNumberController@destroy] Cannot delete default/attached gateway number', $traceId, $timer->lap());

                return redirect()->route('whatsappnumber.index')
                    ->with('error', trans('whatsapp.number.notify.error-delete-default-number', ['number' => $whatsappnumber->number]));
            }

            $deletedNumber = $whatsappnumber->number;
            $whatsappnumber->forceDelete();

            DB::commit();

            LogHelper::logInfo(null, [
                'number_id' => $whatsappnumber->id,
                'number'    => $deletedNumber,
            ], 200, '[WhatsAppNumberController@destroy] Number force-deleted successfully', $traceId, $timer->lap());

            return redirect()->route('whatsappnumber.index')
                ->with('success', trans('whatsapp.number.notify.success-delete-number', ['number' => $deletedNumber]));
        } catch (Throwable $e) {
            DB::rollBack();

            $statusCode = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() <= 599)
                ? (int) $e->getCode()
                : 500;

            LogHelper::logError(null, [
                'number_id'       => $whatsappnumber->id,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, '[WhatsAppNumberController@destroy] Failed to delete number', $traceId, $timer->lap());

            return back()->with('error', 'Failed to delete WhatsApp number: ' . $e->getMessage());
        }
    }
}
