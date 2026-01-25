<?php

namespace App\Http\Controllers\Messages;

use App\Helpers\MessageResponseNormalizer;
use App\Models\Messaging\MessageEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageEventController extends Controller
{
    public function index(Request $request)
    {
        $query = MessageEvent::query()
            ->with(['customer:id,name,contact']);

        // 🔍 Search nama / nomor
        if ($request->filled('q')) {
            $q = $request->q;

            $query->whereHas('customer', function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('contact', 'like', "%{$q}%");
            });
        }

        // 📅 Filter tanggal
        if ($request->filled('date')) {
            $query->whereDate('sent_at', $request->date);
        }

        // ⏰ Filter jam
        if ($request->filled('time_from')) {
            $query->whereTime('sent_at', '>=', $request->time_from);
        }

        if ($request->filled('time_to')) {
            $query->whereTime('sent_at', '<=', $request->time_to);
        }

        $events = $query
            ->orderByDesc('sent_at')
            ->paginate(20)
            ->withQueryString();

        // ✅ NORMALIZE TIDAK DIHILANGKAN
        $events->getCollection()->transform(function ($event) {
            $event->response_summary =
                MessageResponseNormalizer::summarize($event->response_message);

            return $event;
        });

        return view('message_events.index', compact('events'));
    }
}
