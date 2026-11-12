<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\WhatsApp\WhatsAppConversation;
use App\Models\WhatsApp\WhatsAppWebhookMessage;
use App\Services\WhatsApp\CloudAPI\Single\ReplyMessage;
use App\Services\WhatsApp\WhatsAppGatewayResolver;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhatsAppConversationController extends Controller
{
    public function __construct(
        private ReplyMessage $replyMessageService,
        private WhatsAppGatewayResolver $gatewayResolver,
    ) {}

    public function index()
    {
        // 1. Ambil data user yang sedang login saat ini
        $currentUser = auth()->user();

        // 2. Cek role user
        $isKasir = $currentUser->hasRole('Kasir');
        $isSuperAdmin = $currentUser->hasRole('Super Admin');

        $query = WhatsAppConversation::with([
            'customer',
            'messages.media'
        ]);

        // 3. JIKA BUKAN KASIR DAN BUKAN SUPER ADMIN (Artinya dia Admin biasa)
        // Filter agar hanya dapat melihat percakapan dari customer miliknya sendiri
        if (!$isKasir && !$isSuperAdmin) {
            $query->whereHas('customer', function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id);
            });
        }

        // 4. Lanjutkan proses sorting dan mapping JSON seperti biasa
        $conversations = $query->orderByDesc('has_unread')
            ->orderByDesc('last_message_at')
            ->get();

        $conversationsJson = $conversations->map(function ($c) {

            // --- LOGIKA MENGHITUNG ANGKA UNREAD SECARA DINAMIS ---
            $unreadCount = 0;

            if ($c->has_unread) {
                $sortedMessages = $c->messages->sortByDesc('timestamp_wa');

                foreach ($sortedMessages as $msg) {
                    if ($msg->direction === 'incoming') {
                        $unreadCount++;
                    } else {
                        break;
                    }
                }

                if ($unreadCount === 0) {
                    $unreadCount = 1;
                }
            }

            return [
                'id' => $c->id,
                'customer_name' => $c->customer_name,
                'customer_number' => $c->customer_number,
                'last_message_at' => $c->last_message_at?->timestamp,
                'has_unread' => $c->has_unread,
                'unread_count' => $unreadCount,
                'messages' => $c->messages->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'wa_message_id' => $m->wa_message_id,
                        'text_body' => $m->text_body,
                        'direction' => $m->direction,
                        'status' => $m->status,
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

    public function sendMessage(Request $request)
    {
        $traceId = Str::uuid()->toString();

        // 1. Validasi Input Awal
        $validated = $request->validate([
            'conversation_id' => 'required|exists:whatsapp_conversations,id',
            'message'         => 'required|string|max:4000', // Batas karakter WA
            'reply_to_id'     => 'nullable|string',
        ]);

        try {
            LogHelper::logInfo(null, ['validated' => $validated], 200, "[ChatController] Initializing send message", $traceId);

            // 2. Resolve Gateway & Account
            $resolved = $this->gatewayResolver->resolveActiveGatewayAndAccount('meta_reply', $traceId);

            if (!$resolved || empty($resolved[1])) {
                throw new \RuntimeException("No active gateway or account found for meta_reply");
            }

            [$gateway, $account, $number] = $resolved;

            // 3. Cari Data Conversation
            $conversation = WhatsAppConversation::findOrFail($validated['conversation_id']);
            $customerContact = $conversation->customer_number;

            if (!$customerContact) {
                throw new \RuntimeException("Customer contact not found for conversation ID: {$validated['conversation_id']}");
            }

            // 4. Format Recipient
            $formatter = new FormatReceiver;
            $recipient = $formatter->formatReceiver($customerContact, null, false);

            // 5. Logic Context (Reply) - Full Control from Frontend
            $waMessageId = $request->input('reply_to_id') ?: null;

            if ($waMessageId) {
                LogHelper::logInfo(null, ['wa_message_id' => $waMessageId], 200, "[ChatController] Replying to specific message", $traceId);
            } else {
                LogHelper::logInfo(null, ['recipient' => $recipient], 200, "[ChatController] Sending regular text message (no quote)", $traceId);
            }

            // 6. Eksekusi Pengiriman
            $result = $this->replyMessageService->send(
                account: $account,
                contact: $recipient,
                text: $validated['message'],
                replyToMessageId: $waMessageId // Bisa berisi ID spesifik atau null (pesan biasa)
            );

            // 7. Ambil WA ID dari Response Meta
            $metaMessageId = data_get($result->response, 'body.messages.0.id');

            LogHelper::logInfo(null, [
                'recipient' => $recipient,
                'wa_id' => $metaMessageId,
                'gateway' => $account->name
            ], 200, "[ChatController] Message dispatched successfully", $traceId);

            return response()->json([
                'status'  => 'success',
                'message' => 'Pesan berhasil dikirim',
                'wa_id'   => $metaMessageId
            ]);
        } catch (\Throwable $e) {
            // 8. Error Handling & Traceable Log
            LogHelper::logError(null, [
                'conversation_id' => $request->conversation_id,
                'exception' => get_class($e),
                'error'     => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ], 500, "[ChatController] Critical error in sendMessage", $traceId);

            // Berikan pesan yang user-friendly di production
            $errorMessage = config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan sistem saat mengirim pesan.';

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengirim pesan: ' . $errorMessage
            ], 500);
        }
    }

    public function sync(Request $request)
    {
        $conversationId = $request->query('conversation_id');
        $lastId = $request->query('last_id');

        // 1. Ambil hanya pesan yang lebih baru dari last_id
        $newMessages = WhatsAppWebhookMessage::with(['media'])
            ->where('conversation_id', $conversationId)
            ->when($lastId, function ($query) use ($lastId) {
                // Mengambil pesan yang ID-nya lebih besar dari ID terakhir yang ada di browser
                return $query->where('id', '>', $lastId);
            })
            ->orderBy('id', 'asc')
            ->get();

        // 2. Transformasi dengan struktur yang identik dengan method index()
        $formattedMessages = $newMessages->map(function ($m) {
            return [
                'id' => $m->id,
                'wa_message_id' => $m->wa_message_id,
                'text_body' => $m->text_body,
                'direction' => $m->direction,
                'status' => $m->status,
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
        })->values()->all();

        return response()->json([
            'new_messages' => $formattedMessages
        ]);
    }

    public function markAsRead(int $id)
    {
        LogHelper::logInfo(null, ['conversation_id' => $id], 200, "[ChatController] Marking conversation as read", Str::uuid()->toString());
        // Cari percakapan berdasarkan ID
        $conversation = WhatsAppConversation::find($id);

        if ($conversation) {
            // Update status di database (sesuaikan nama kolomnya)
            $conversation->update(['has_unread' => false, 'status' => 'read']); // Ganti 'has_unread' dengan nama kolom yang sesuai
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }
}
