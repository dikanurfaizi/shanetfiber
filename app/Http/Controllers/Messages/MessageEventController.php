<?php

namespace App\Http\Controllers\Messages;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Helpers\MessageResponseNormalizer;
use App\Models\Messaging\MessageEvent;
use App\Http\Controllers\Controller;
use App\Jobs\SendResentMessageJob;
use App\Services\MessageEvent\ResendEventService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessageEventController extends Controller
{
    public function index(Request $request)
    {
        $query = MessageEvent::query()
            ->with(['customer:id,name,contact']);

        // 🔍 Search nama / nomor customer
        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('customer', function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('contact', 'like', "%{$q}%");
            });
        }

        // 📡 Filter channel
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        // 🏷 Filter message type
        if ($request->filled('message_type')) {
            $query->where('message_type', $request->message_type);
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

        // 🚦 Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $events = $query
            ->orderByDesc('sent_at')
            ->paginate(20)
            ->withQueryString();

        // 🧠 Normalize response (tidak dihilangkan)
        $events->getCollection()->transform(function ($event) {
            $event->response_summary = MessageResponseNormalizer::summarize(
                $event->response_message
            );
            return $event;
        });

        // 📦 Dropdown options (ambil dari DB)
        $channels = MessageEvent::query()
            ->select('channel')
            ->distinct()
            ->orderBy('channel')
            ->pluck('channel');

        $messageTypes = MessageEvent::query()
            ->select('message_type')
            ->distinct()
            ->orderBy('message_type')
            ->pluck('message_type');

        $statuses = MessageEvent::query()
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return view(
            'message_events.index',
            compact('events', 'channels', 'messageTypes', 'statuses')
        );
    }

    public function resend(MessageEvent $event)
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();

        // Log awal proses resend
        LogHelper::logInfo(null, [
            'trace_id' => $traceId,
            'event_id' => $event->id,
            'current_status' => $event->status
        ], 200, 'Initiating message resend process.', $traceId, $timer->lap());

        if ($event->status !== 'failed') {
            // Log jika event tidak valid untuk di-resend
            LogHelper::logWarning(null, [
                'trace_id' => $traceId,
                'event_id' => $event->id,
                'current_status' => $event->status
            ], 400, 'Event not eligible for resend.', $traceId, $timer->lap());

            return back()->with('error', 'Event not eligible for resend.');
        }

        SendResentMessageJob::dispatch(
            $event->id,
            $traceId
        )->onQueue('default');

        // Log sukses masuk antrean
        LogHelper::logInfo(null, [
            'trace_id' => $traceId,
            'event_id' => $event->id,
            'queue' => 'default'
        ], 200, 'Message resend job dispatched successfully.', $traceId, $timer->lap());

        return back()->with('success', 'Event queued for resend.');
    }
}
