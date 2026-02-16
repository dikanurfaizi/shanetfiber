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
        $conversations = WhatsappConversation::with(['messages', 'customer'])
            ->orderBy('last_message_at', 'desc')
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
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return view('whatsapp.conversations.index', compact('conversations', 'selectedConversation', 'conversationsJson'));
    }
}
