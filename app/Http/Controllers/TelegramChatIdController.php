<?php

namespace App\Http\Controllers;

use App\Models\TelegramGateway\TelegramGateway;
use App\Models\Websystem;
use App\Models\User;
use App\Models\TelegramGateway\TelegramChatId;
use App\Services\TelegramMessageService;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramChatIdController extends Controller
{
    private TelegramMessageService $messageService;

    public function __construct()
    {
        $this->messageService = app(TelegramMessageService::class);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::select('id', 'name')->get();
        $rawSearch = $request->input('search');
        $search = ltrim($rawSearch, '@');

        $chats = TelegramChatId::with('user')
            ->when($search, function ($query) use ($search) {
                $query->where('chat_id', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->paginate(10);

        return view('telegram_gateways.chats.index', compact('users', 'chats'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function store(Request $request)
    {
        $chatIds = $request->input('chat_ids', []);

        foreach ($chatIds as $data) {
            TelegramChatId::updateOrCreate(
                ['chat_id' => $data['chat_id']],
                [
                    'username'   => $data['username'] ?? null,
                    'first_name' => $data['first_name'] ?? null,
                    'last_name'  => $data['last_name'] ?? null,
                    'disabled'   => false,
                    'slug' => now()->format('YmdHis') . ($data['username'] ?? $data['first_name'] ?? 'chat'),
                ]
            );
        }

        return response()->json(['message' => 'Chat ID berhasil disimpan.']);
    }

    public function update_telegram_data($id, Request $request)
    {
        $chat = TelegramChatId::findOrFail($id);

        $request->validate([
            'user_id' => 'nullable|exists:users,id',
        ]);

        $chat->update([
            'user_id' => $request->input('user_id'),
        ]);

        return response()->json(['message' => 'User Telegram berhasil diperbarui.']);
    }

    public function destroy(Request $request, TelegramChatId $telegramchat)
    {
        dd($telegramchat);
        Log::info('Masuk ke destroy');

        $telegramchat->deleted_by = auth()->id();
        $telegramchat->deleted_at = now();
        $telegramchat->save();

        return redirect()->route('telegramchat.index')->with('success', 'Data berhasil dihapus (soft delete).');
    }

    public function softDelete($id)
    {
        $chat = TelegramChatId::findOrFail($id);

        $chat->update([
            'deleted_by' => auth()->id(),
        ]);

        $chat->delete(); // Ini akan set deleted_at otomatis karena pakai SoftDeletes

        return redirect()->route('telegramchat.index')->with('success', 'Data berhasil dihapus.');
    }

    public function show()
    {
        $chatIds = [
            [
                'chat_id'    => 123456789,
                'username'   => 'johndoe',
                'first_name' => 'John',
                'last_name'  => 'Doe',
            ],
            [
                'chat_id'    => 987654321,
                'username'   => 'janedoe',
                'first_name' => 'Jane',
                'last_name'  => 'Doe',
            ],
        ];

        return response()->json([
            'message' => 'Data dummy berhasil diambil dari Telegram',
            'chat_ids' => $chatIds,
        ]);
    }


    public function get_updates()
    {
        try {
            $chatIds = $this->messageService->findNewChatIds();

            if ($chatIds->isEmpty()) {
                Log::info('⚠️ Tidak ada chat ID baru.');
                return response()->json(['message' => 'Tidak ada chat ID baru dari Telegram']);
            }

            return response()->json([
                'message' => 'Data berhasil diambil dari Telegram',
                'chat_ids' => $chatIds,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Terjadi error di get_updates:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Gagal memproses data Telegram: ' . $e->getMessage()], 500);
        }
    }

    public function test_message($id)
    {
        $traceId = (string) Str::uuid();

        $chat = TelegramChatId::find($id);

        try {
            $websystem = Websystem::first();
            $title = $websystem->title ?? 'ShaNet Fiber Management';

            $message = "Hai! Terima kasih telah berinteraksi dengan Official Telegram\n\n" .
                "Salam,\n{$title}";

            $this->messageService->send(
                $chat->chat_id,
                $message,
                $traceId
            );

            return redirect()->route('telegramgateway.index')
                ->with('success', trans('whatsapp.gateway.notify.test-success'));
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['message_error' => $e->getMessage()],
                500,
                "Error to send WhatsApp test message",
                $traceId
            );

            LogHelper::logWarning(
                null,
                ['trace_as_string' => $e->getTraceAsString()],
                500,
                "Exception trace",
                $traceId
            );

            return redirect()->route('telegramgateway.index')
                ->with('error', 'Terjadi kesalahan saat mengirim pesan uji coba. Silakan periksa log.');
        }
    }
}
