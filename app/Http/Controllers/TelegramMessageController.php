<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Telegram\TelegramMessage;

class TelegramMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $messages = TelegramMessage::orderBy('name', 'ASC')->paginate(20);
        return view('telegram_gateways.messages.index', compact('messages'))->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('telegram_gateways.messages.create');
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
        $message = new TelegramMessage();
        $message->name = $validated['name'];
        $message->incoming_message = $validated['incoming_message'];
        $message->reply_message = $validated['reply_message'];
        $message->description = $validated['description'] ?? null;
        $message->slug = $slug;
        $message->default = true;
        $message->save();

        // Redirect kembali dengan pesan sukses
        return redirect()
            ->route('telegrammessage.index') // ganti ke route tujuan setelah create
            ->with('success', __('telegram.message.notify.success-save-message'));
    }

    /**
     * Display the specified resource.
     */
    public function show(TelegramMessage $telegrammessage, Request $request)
    {

        $message = $telegrammessage->reply_message;
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

        return view('telegram_gateways.messages.show', [
            'data' => $telegrammessage,
            'preview_messagge' => $send_message
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TelegramMessage $telegrammessage)
    {
        // dd($telegrammessage);
        return view('telegram_gateways.messages.edit', [
            'data' => $telegrammessage
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TelegramMessage $telegrammessage)
    {
        if ($telegrammessage->default) {

            $this->validate(
                $request,
                [
                    'name'                  => ['required', 'max:255', Rule::unique('telegram_messages')->ignore($telegrammessage->slug, 'slug')],
                    'reply_message'        => 'required'
                ]
            );
        } else {

            $this->validate(
                $request,
                [
                    'name'                  => ['required', 'max:255', Rule::unique('telegram_messages')->ignore($telegrammessage->slug, 'slug')],
                    'incoming_message'      => ['required', Rule::unique('telegram_messages')->ignore($telegrammessage->slug, 'slug')],
                    'reply_message'        => 'required'
                ],
                [

                    'incoming_message.unique' => trans('telegram.message.validator.incoming-message-hasben-taken'),
                    'incoming_message.required' => trans('telegram.message.validator.incoming-message-required'),
                ]
            );
        }
        $telegrammessage->name = strip_tags($request->input('name'));
        if ($telegrammessage->default == false) {
            $telegrammessage->incoming_message = strip_tags($request->input('incoming_message'));
            // $telegrammessage->telegram_number_id = $request->input('telegram_number');
        }

        $telegrammessage->reply_message = strip_tags($request->input('reply_message'));
        $telegrammessage->description = strip_tags($request->input('description'));
        $telegrammessage->save();
        return redirect()->route('telegrammessage.index')
            ->with('success', trans('telegram.message.notify.success-update-message', ['name' => $telegrammessage->name]));
    }


    public function disable_message(Request $request, TelegramMessage $telegrammessage)
    {
        if ($telegrammessage->disabled == true) {
            $telegrammessage->disabled = false;
        } else {
            $telegrammessage->disabled = true;
        }
        $telegrammessage->save();
        return redirect()->route('telegrammessage.index')
            ->with('success', trans('telegram.message.notify.success-update-message', ['name' => $telegrammessage->name]));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function delete(TelegramMessage $telegrammessage)
    {
        return view('telegram_gateways.messages.delete', compact('telegrammessage'));
    }

    public function destroy(TelegramMessage $telegrammessage)
    {
        if ($telegrammessage->default) {
            return redirect()->route('telegrammessage.index')
                ->with('error', trans('telegram.message.notify.error-delete-default-message', ['name' => $telegrammessage->name]));
        } else {
            // $telegrammessage->delete();
            $telegrammessage->forceDelete();
            return redirect()->route('telegrammessage.index')
                ->with('success', trans('telegram.message.notify.success-delete-message', ['name' => $telegrammessage->name]));
        }
    }
}
