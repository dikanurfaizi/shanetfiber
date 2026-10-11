<?php

namespace App\Http\Controllers\WhatsApp;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\WhatsApp\WhatsAppMessage;

class WhatsAppMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $messages = WhatsAppMessage::orderBy('name', 'ASC')->paginate(20);
        return view('whatsapp_gateways.messages.index', compact('messages'))->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('whatsapp_gateways.messages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'incoming_message' => 'required|string|max:255',
            'reply_message'    => 'required|string',
            'description'      => 'nullable|string|max:255',
        ]);

        // Generate slug dari incoming_message
        $slug = Str::of($validated['incoming_message'])->lower()->replace(' ', '_');

        // Simpan ke database
        $message = new WhatsAppMessage();
        $message->name = $validated['name'];
        $message->incoming_message = $validated['incoming_message'];
        $message->reply_message = $validated['reply_message'];
        $message->description = $validated['description'] ?? null;
        $message->slug = $slug;
        $message->default = true;
        $message->save();

        // Redirect kembali dengan pesan sukses
        return redirect()
            ->route('whatsappmessage.index') // ganti ke route tujuan setelah create
            ->with('success', __('whatsapp.message.notify.success-save-message'));
    }

    /**
     * Display the specified resource.
     */
    public function show(WhatsAppMessage $whatsappmessage, Request $request)
    {

        $message = $whatsappmessage->reply_message;
        // $message = str_replace("\n", "\n", $message);
        $replace = [
            '%name%' => 'Shanum',
            '%customer_id%' => 'WND-101',
            '%address%' => 'Ciseureuh, Purwakarta',
            '%day%' => 2,
            '%packages%' => 'Package',
            '%customer_service%' => Auth::user()->name,
            '%email%' => Auth::user()->email,

            // '/n' => '<br>',
        ];
        $send_message =  str_replace(array_keys($replace), $replace, $message);

        return view('whatsapp_gateways.messages.show', [
            'data' => $whatsappmessage,
            'preview_messagge' => $send_message
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WhatsAppMessage $whatsappmessage)
    {

        return view('whatsapp_gateways.messages.edit', [
            'data' => $whatsappmessage
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WhatsAppMessage $whatsappmessage)
    {
        if ($whatsappmessage->default) {

            $this->validate(
                $request,
                [
                    'name'                  => ['required', 'max:255', Rule::unique('whatsapp_messages')->ignore($whatsappmessage->slug, 'slug')],
                    'reply_message'        => 'required'
                ]
            );
        } else {

            $this->validate(
                $request,
                [
                    'name'                  => ['required', 'max:255', Rule::unique('whatsapp_messages')->ignore($whatsappmessage->slug, 'slug')],
                    'incoming_message'      => ['required', Rule::unique('whatsapp_messages')->ignore($whatsappmessage->slug, 'slug')],
                    'reply_message'        => 'required'
                ],
                [

                    'incoming_message.unique' => trans('whatsapp.message.validator.incoming-message-hasben-taken'),
                    'incoming_message.required' => trans('whatsapp.message.validator.incoming-message-required'),
                ]
            );
        }
        $whatsappmessage->name = strip_tags($request->input('name'));
        if ($whatsappmessage->default == false) {
            $whatsappmessage->incoming_message = strip_tags($request->input('incoming_message'));
            // $whatsappmessage->whatsapp_number_id = $request->input('whatsapp_number');
        }

        $whatsappmessage->reply_message = strip_tags($request->input('reply_message'));
        $whatsappmessage->description = strip_tags($request->input('description'));
        $whatsappmessage->save();
        return redirect()->route('whatsappmessage.index')
            ->with('success', trans('whatsapp.message.notify.success-update-message', ['name' => $whatsappmessage->name]));
    }


    public function disable_message(Request $request, WhatsAppMessage $whatsappmessage)
    {
        if ($whatsappmessage->disabled == true) {
            $whatsappmessage->disabled = false;
        } else {
            $whatsappmessage->disabled = true;
        }
        $whatsappmessage->save();
        return redirect()->route('whatsappmessage.index')
            ->with('success', trans('whatsapp.message.notify.success-update-message', ['name' => $whatsappmessage->name]));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function delete(WhatsAppMessage $whatsappmessage)
    {
        return view('whatsapp_gateways.messages.delete', compact('whatsAppMessage'));
    }
    public function destroy(WhatsAppMessage $whatsappmessage)
    {
        if ($whatsappmessage->default) {
            return redirect()->route('whatsappmessage.index')
                ->with('error', trans('whatsapp.message.notify.error-delete-default-message', ['name' => $whatsappmessage->name]));
        } else {
            // $whatsappmessage->delete();
            $whatsappmessage->forceDelete();
            return redirect()->route('whatsappmessage.index')
                ->with('success', trans('whatsapp.message.notify.success-delete-message', ['name' => $whatsappmessage->name]));
        }
    }
}
