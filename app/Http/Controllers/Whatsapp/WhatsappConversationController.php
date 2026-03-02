<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\Whatsapp\WhatsappConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsappConversationController extends Controller
{
    public function index()
    {
        $conversations = WhatsappConversation::with([
            'customer',
            'messages.media'
        ])
            ->orderByDesc('last_message_at')
            ->get();

        $selectedConversation = $conversations->first() ?? null;

        $conversationsJson = $conversations->map(function ($c) {
            return [
                'id' => $c->id,
                'customer_name' => $c->customer_name, // accessor
                'customer_number' => $c->customer_number,
                'last_message_at' => $c->last_message_at?->timestamp,
                'has_unread' => $c->has_unread,
                'messages' => $c->messages->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'text_body' => $m->text_body,
                        'direction' => $m->direction,
                        'timestamp_wa' => $m->timestamp_wa?->timestamp,
                        'media' => $m->media->map(function ($media) {
                            return [
                                'id' => $media->id,
                                'file_name' => $media->file_name,
                                'mime_type' => $media->mime_type,
                                'url' => route('whatsapp.media.show', $media->id),
                            ];
                        })->values()->all(),
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return view('whatsapp.conversations.index', [
            'conversationsJson' => $conversationsJson,
        ]);
    }
}
