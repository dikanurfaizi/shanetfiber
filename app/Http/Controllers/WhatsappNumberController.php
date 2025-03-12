<?php

namespace App\Http\Controllers;

use App\Models\WhatsappGateway\WhatsappNumber;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\WhatsappGateway\WhatsappGateway;

class WhatsappNumberController extends Controller
{
    public function formatPhoneNumber($number, $gateway)
    {
        // Default country code ke 62 jika null
        $country_code = $gateway->country_code ?? '62';

        // Hapus semua karakter non-digit kecuali "+"
        $number = preg_replace('/[^\d+]/', '', $number);

        // Jika nomor diawali dengan "+", hapus "+" untuk diproses lebih lanjut
        if (str_starts_with($number, '+')) {
            $number = substr($number, 1);
        }

        // Hapus semua tanda "-" jika ada
        $number = str_replace('-', '', $number);

        // Jika nomor diawali dengan "62", ubah ke country_code
        if (str_starts_with($number, '62')) {
            $number = preg_replace('/^62/', $country_code, $number);
        }
        // Jika nomor diawali dengan "0", ubah ke format country_code
        elseif (str_starts_with($number, '0')) {
            $number = preg_replace('/^0/', $country_code, $number);
        }
        // Jika nomor tidak diawali "62" atau "0", berarti kemungkinan typo atau salah format
        else {
            $number = $country_code . $number;
        }

        return $number;
    }

    private function getGateway()
    {
        $gateway = WhatsappGateway::where('slug', 'gateway')->first();
        return $gateway;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $numbers = WhatsappNumber::orderBy('name', 'ASC')->paginate(10);
        return view('whatsapp_gateways.numbers.index', compact('numbers'))->with('i', (request()->input('page', 1) - 1) * 10);
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
        $this->validate(
            $request,
            [
                'name'          => 'required|max:255|unique:whatsapp_numbers',
                'number'        => 'required|numeric|unique:whatsapp_numbers',
                // 'header_name'   => 'required',
                // 'wa_secret'     => 'required',
            ],
            [
                'name.unique' => 'Name ' . $request->name . ' already exist.'

            ]
        );

        $gateway = $this->getGateway();
        $number = $this->formatPhoneNumber($request->input('number'), $gateway);
        $header_name = 'Shanet-Signature';
        $number = WhatsappNumber::create([
            'slug'          => str(Str::random(5))->slug(),
            'name'          => $request->input('name'),
            'number'        => $number,
            // 'webhook_client_header'        => $request->input('header_name'),
            // 'webhook_client_secret'        => $request->input('wa_secret'),
            'webhook_client_header'        => $header_name,
            'webhook_client_secret'        => str(Str::random(15)),
            'description'   => $request->input('description'),
        ]);

        return redirect()->route('whatsappnumber.index')
            ->with('success', trans('whatsapp.number.notify.success-save-number', ['number' => $number->number]));
    }

    /**
     * Display the specified resource.
     */
    public function show(WhatsappNumber $whatsappnumber)
    {
        //return dd($whatsappnumber->name);
        $computedSignature = hash_hmac('sha512', $whatsappnumber->number, $whatsappnumber->webhook_client_secret);
        return view('whatsapp_gateways.numbers.show', [
            'data' => $whatsappnumber,
            'secret' => $computedSignature
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WhatsappNumber $whatsappnumber)
    {

        return view('whatsapp_gateways.numbers.edit', [
            'data' => $whatsappnumber
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WhatsappNumber $whatsappnumber)
    {

        $this->validate(
            $request,
            [
                'name'      => ['required', Rule::unique('whatsapp_numbers')->ignore($whatsappnumber->slug, 'slug')],
                'number'     => ['required', Rule::unique('whatsapp_numbers')->ignore($whatsappnumber->slug, 'slug')],
            ],
            [
                'name.unique' => 'Name ' . $request->name . ' already exist.'

            ]
        );
        $gateway = $this->getGateway();
        $number = $this->formatPhoneNumber($request->input('number'), $gateway);
        $whatsappnumber->name = $request->input('name');
        $whatsappnumber->number = $number;
        $whatsappnumber->description = $request->input('description');
        $whatsappnumber->save();
        return redirect()->route('whatsappnumber.index')
            ->with('success', trans('whatsapp.number.notify.success-update-number', ['name' => $whatsappnumber->name]));
    }
    public function disable_number(Request $request, WhatsappNumber $whatsappnumber)
    {
        // return dd($whatsappnumber);
        if ($whatsappnumber->disabled == true) {
            $whatsappnumber->disabled = false;
        } else {
            $whatsappnumber->disabled = true;
        }
        $whatsappnumber->save();
        return redirect()->route('whatsappnumber.index')
            ->with('success', trans('whatsapp.number.notify.success-update-number', ['name' => $whatsappnumber->name]));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function delete(WhatsappNumber $whatsappnumber)
    {
        return view('whatsapp_gateways.numbers.delete', compact('whatsappnumber'));
    }
    public function destroy(WhatsappNumber $whatsappnumber)
    {
        // return dd(count($whatsappnumber->whatsapp_gateways));
        if (count($whatsappnumber->whatsapp_gateways)) {
            // return dd('Test');
            return redirect()->route('whatsappnumber.index')
                ->with('success', trans('whatsapp.number.notify.error-delete-default-number', ['number' => $whatsappnumber->number]));
        } else {
            // $whatsappnumber->delete();
            $whatsappnumber->forceDelete();
            return redirect()->route('whatsappnumber.index')
                ->with('success', trans('whatsapp.number.notify.success-delete-number', ['number' => $whatsappnumber->number]));
        }
    }
}
